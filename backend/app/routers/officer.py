"""Endpoint petugas (semua wajib JWT). Semua aksi sensitif dicatat ke audit log."""

from __future__ import annotations

import asyncio
import json
import logging
from datetime import datetime, timezone
from pathlib import Path
from typing import Annotated, Any

from fastapi import APIRouter, Depends, HTTPException, Query, Request, status
from fastapi.responses import FileResponse, StreamingResponse
from sqlalchemy import case, func, or_, select, update
from sqlalchemy.orm import Session, selectinload
from starlette.concurrency import run_in_threadpool

from app.database import SessionLocal, get_db
from app.deps import get_current_officer, require_role
from app.models import Attachment, AuditLog, FollowUpMessage, InternalNote, Officer, Report, utcnow
from app.schemas import (
    AssignRequest,
    AttachmentOut,
    AuditLogOut,
    CategoryStat,
    KeyCount,
    MessageOut,
    NoteCreate,
    NoteOut,
    OfficerMessageCreate,
    OfficerOut,
    OverrideRequest,
    OverrideResponse,
    PatternOut,
    ReportDetail,
    ReportListOut,
    ReportStatus,
    StatsOut,
    StatusUpdate,
    Urgency,
    UrgencyUpdate,
)
from app.security import (
    PinLockedError,
    decrypt_text,
    override_pin_guard,
)
from app.services import audit

logger = logging.getLogger(__name__)
router = APIRouter(prefix="/api/officer", tags=["Petugas"])

_URGENCY_ORDER = {"MERAH": 0, "KUNING": 1, "HIJAU": 2}


def _ensure_utc(value: datetime) -> datetime:
    """SQLite mengembalikan datetime naive; anggap UTC agar aritmetika konsisten."""
    return value if value.tzinfo is not None else value.replace(tzinfo=timezone.utc)


def _hours_between(start: datetime, end: datetime) -> float:
    return max((_ensure_utc(end) - _ensure_utc(start)).total_seconds(), 0) / 3600


def _waiting_hours(report: Report) -> float:
    return round(_hours_between(report.created_at, datetime.now(timezone.utc)), 2)


def _to_list_item(report: Report) -> dict[str, Any]:
    return {
        "id": report.id,
        "ticket_code": report.ticket_code,
        "summary": report.summary,
        "category": report.category,
        "location": report.location,
        "urgency_ai": report.urgency_ai,
        "urgency_final": report.urgency_final,
        "status": report.status,
        "victim_wish": report.victim_wish,
        "created_at": report.created_at,
        "updated_at": report.updated_at,
        "assigned_to": report.assigned_to,
        "ai_failed": report.ai_failed,
        "waiting_hours": _waiting_hours(report),
    }


# ------------------------------------------------------------------ Daftar


@router.get("/reports", response_model=ReportListOut)
def list_reports(
    officer: Officer = Depends(get_current_officer),
    db: Session = Depends(get_db),
    status_filter: ReportStatus | None = Query(None, alias="status"),
    urgency: Urgency | None = None,
    category: str | None = None,
    location: str | None = None,
    assigned_to: str | None = None,
    q: str | None = None,
    since: datetime | None = None,
    page: int = Query(1, ge=1),
    limit: int = Query(20, ge=1, le=100),
) -> ReportListOut:
    stmt = select(Report)

    if status_filter:
        stmt = stmt.where(Report.status == status_filter.value)
    if urgency:
        stmt = stmt.where(Report.urgency_final == urgency.value)
    if category:
        stmt = stmt.where(Report.category == category)
    if location:
        stmt = stmt.where(Report.location.ilike(f"%{location}%"))
    if assigned_to:
        stmt = stmt.where(Report.assigned_to == assigned_to)
    if q:
        like = f"%{q}%"
        stmt = stmt.where(or_(Report.sanitized_text.ilike(like), Report.summary.ilike(like)))
    if since:
        stmt = stmt.where(Report.created_at > _ensure_utc(since))

    total = db.scalar(select(func.count()).select_from(stmt.subquery())) or 0

    urgency_rank = case(_URGENCY_ORDER, value=Report.urgency_final, else_=9)
    stmt = (
        stmt.order_by(urgency_rank.asc(), Report.created_at.asc())
        .offset((page - 1) * limit)
        .limit(limit)
    )
    reports = db.scalars(stmt).all()

    # Sengaja tanpa audit log: endpoint ini dipanggil berulang (polling/SSE) dan
    # akses data korban spesifik sudah diaudit di detail/unduh lampiran/override.
    return ReportListOut(
        items=[_to_list_item(r) for r in reports],
        total=total,
        page=page,
        limit=limit,
    )


# ------------------------------------------------------------------ Detail


def _load_report(db: Session, report_id: str) -> Report:
    """Ambil laporan + relasi dengan eager load. 404 bila tidak ada."""
    report = db.scalar(
        select(Report)
        .where(Report.id == report_id)
        .options(
            selectinload(Report.attachments),
            selectinload(Report.notes),
            selectinload(Report.messages),
        )
    )
    if report is None:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Laporan tidak ditemukan.")
    return report


def _build_detail(report: Report) -> ReportDetail:
    return ReportDetail(
        **_to_list_item(report),
        sanitized_text=report.sanitized_text,
        urgency_rationale=report.urgency_rationale,
        contact_available=report.contact_enc is not None,
        attachments=[AttachmentOut.model_validate(a) for a in report.attachments],
        notes=[NoteOut.model_validate(n) for n in sorted(report.notes, key=lambda n: n.created_at)],
        messages=[
            MessageOut.model_validate(m) for m in sorted(report.messages, key=lambda m: m.created_at)
        ],
    )


@router.get("/reports/{report_id}", response_model=ReportDetail)
def get_report(
    report_id: str,
    officer: Officer = Depends(get_current_officer),
    db: Session = Depends(get_db),
) -> ReportDetail:
    report = _load_report(db, report_id)

    audit.log_action(db, audit.ACTION_VIEW_REPORT, officer_id=officer.id, report_id=report.id)
    db.commit()

    return _build_detail(report)


# ------------------------------------------------------------------ Mutasi


@router.patch("/reports/{report_id}/status", response_model=ReportDetail)
def update_status(
    report_id: str,
    payload: StatusUpdate,
    officer: Officer = Depends(get_current_officer),
    db: Session = Depends(get_db),
) -> ReportDetail:
    report = _require_report(db, report_id)
    old = report.status
    report.status = payload.status.value
    audit.log_action(
        db,
        audit.ACTION_CHANGE_STATUS,
        officer_id=officer.id,
        report_id=report.id,
        detail={"dari": old, "ke": payload.status.value},
    )
    db.commit()
    return _build_detail(_load_report(db, report_id))


@router.patch("/reports/{report_id}/urgency", response_model=ReportDetail)
def update_urgency(
    report_id: str,
    payload: UrgencyUpdate,
    officer: Officer = Depends(get_current_officer),
    db: Session = Depends(get_db),
) -> ReportDetail:
    report = _require_report(db, report_id)
    old = report.urgency_final
    report.urgency_final = payload.urgency.value
    audit.log_action(
        db,
        audit.ACTION_CHANGE_URGENCY,
        officer_id=officer.id,
        report_id=report.id,
        detail={"dari": old, "ke": payload.urgency.value, "alasan": payload.reason},
    )
    db.commit()
    return _build_detail(_load_report(db, report_id))


@router.patch("/reports/{report_id}/assign", response_model=ReportDetail)
def assign_report(
    report_id: str,
    payload: AssignRequest,
    officer: Officer = Depends(get_current_officer),
    db: Session = Depends(get_db),
) -> ReportDetail:
    report = _require_report(db, report_id)
    if payload.officer_id is not None:
        target = db.get(Officer, payload.officer_id)
        if target is None or not target.is_active:
            raise HTTPException(status_code=status.HTTP_400_BAD_REQUEST, detail="Petugas tujuan tidak valid.")
    report.assigned_to = payload.officer_id
    audit.log_action(
        db,
        audit.ACTION_ASSIGN,
        officer_id=officer.id,
        report_id=report.id,
        detail={"assigned_to": payload.officer_id},
    )
    db.commit()
    return _build_detail(_load_report(db, report_id))


@router.post("/reports/{report_id}/notes", response_model=NoteOut, status_code=status.HTTP_201_CREATED)
def add_note(
    report_id: str,
    payload: NoteCreate,
    officer: Officer = Depends(get_current_officer),
    db: Session = Depends(get_db),
) -> NoteOut:
    report = _require_report(db, report_id)
    note = InternalNote(report_id=report.id, officer_id=officer.id, note=payload.note)
    db.add(note)
    audit.log_action(db, audit.ACTION_ADD_NOTE, officer_id=officer.id, report_id=report.id)
    db.commit()
    db.refresh(note)
    return NoteOut.model_validate(note)


@router.post(
    "/reports/{report_id}/messages", response_model=MessageOut, status_code=status.HTTP_201_CREATED
)
def send_officer_message(
    report_id: str,
    payload: OfficerMessageCreate,
    officer: Officer = Depends(get_current_officer),
    db: Session = Depends(get_db),
) -> MessageOut:
    report = _require_report(db, report_id)
    message = FollowUpMessage(report_id=report.id, sender="SATGAS", body=payload.body)
    report.updated_at = utcnow()
    db.add(message)
    audit.log_action(db, audit.ACTION_SEND_MESSAGE, officer_id=officer.id, report_id=report.id)
    db.commit()
    db.refresh(message)
    return MessageOut.model_validate(message)


# ------------------------------------------------------------------ Override


@router.post("/reports/{report_id}/override", response_model=OverrideResponse)
def override_identity(
    report_id: str,
    payload: OverrideRequest,
    officer: Officer = Depends(require_role("ADMIN")),
    db: Session = Depends(get_db),
) -> OverrideResponse:
    if payload.pin != payload.confirm_pin:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST, detail="Konfirmasi PIN tidak cocok."
        )

    report = _require_report(db, report_id)

    try:
        valid = override_pin_guard.check(officer.id, payload.pin)
    except PinLockedError as exc:
        raise HTTPException(status_code=status.HTTP_429_TOO_MANY_REQUESTS, detail=str(exc)) from exc

    if not valid:
        raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="PIN override salah.")

    original_text = decrypt_text(report.original_text_enc) or ""
    contact = decrypt_text(report.contact_enc)

    # JANGAN pernah menulis isi terdekripsi ke audit log.
    audit.log_action(
        db,
        audit.ACTION_OVERRIDE,
        officer_id=officer.id,
        report_id=report.id,
        detail={"alasan": payload.reason, "ticket_code": report.ticket_code, "contact_available": contact is not None},
    )
    db.commit()

    message = (
        "Kontak pelapor dibuka. Gunakan hanya sesuai alasan yang dicatat."
        if contact
        else "Pelapor tidak memberikan kontak. Identitas tidak dapat dihubungi."
    )
    return OverrideResponse(
        report_id=report.id,
        ticket_code=report.ticket_code,
        contact=contact,
        original_text=original_text,
        message=message,
    )


# ------------------------------------------------------------------ Lampiran


@router.get("/reports/{report_id}/attachments/{attachment_id}")
def download_attachment(
    report_id: str,
    attachment_id: str,
    officer: Officer = Depends(get_current_officer),
    db: Session = Depends(get_db),
) -> FileResponse:
    attachment = db.get(Attachment, attachment_id)
    if attachment is None or attachment.report_id != report_id:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Lampiran tidak ditemukan.")
    path = Path(attachment.path)
    if not path.exists():
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="File lampiran tidak tersedia.")
    audit.log_action(db, audit.ACTION_VIEW_REPORT, officer_id=officer.id, report_id=report_id)
    db.commit()
    return FileResponse(path, media_type=attachment.mime, filename=attachment.filename_random)


# ------------------------------------------------------------------ SSE & polling


def _sse(event: str, data: dict[str, Any]) -> str:
    return f"event: {event}\ndata: {json.dumps(data, default=str)}\n\n"


def _fetch_new_reports(since: datetime) -> list[dict[str, Any]]:
    since = _ensure_utc(since)
    db = SessionLocal()
    try:
        reports = db.scalars(
            select(Report).where(Report.created_at > since).order_by(Report.created_at.asc())
        ).all()
        return [
            {
                "id": r.id,
                "ticket_code": r.ticket_code,
                "urgency_final": r.urgency_final,
                "category": r.category,
                "summary": r.summary,
                "created_at": r.created_at,
            }
            for r in reports
        ]
    finally:
        db.close()


@router.get("/stream")
async def stream_reports(
    request: Request,
    officer: Officer = Depends(get_current_officer),
) -> StreamingResponse:
    async def event_generator():
        since = utcnow()
        yield _sse("ping", {"message": "connected"})
        while True:
            if await request.is_disconnected():
                break
            await asyncio.sleep(3)
            reports = await run_in_threadpool(_fetch_new_reports, since)
            if reports:
                for report in reports:
                    yield _sse("report", report)
                since = max(r["created_at"] for r in reports)
            else:
                yield ": keep-alive\n\n"

    return StreamingResponse(
        event_generator(),
        media_type="text/event-stream",
        headers={"Cache-Control": "no-cache", "X-Accel-Buffering": "no"},
    )


# ------------------------------------------------------------------ Statistik & pola


@router.get("/stats", response_model=StatsOut)
def stats(
    officer: Officer = Depends(get_current_officer),
    db: Session = Depends(get_db),
    date_from: datetime | None = Query(None, alias="from"),
    date_to: datetime | None = Query(None, alias="to"),
) -> StatsOut:
    filters = []
    if date_from is not None:
        filters.append(Report.created_at >= _ensure_utc(date_from))
    if date_to is not None:
        filters.append(Report.created_at <= _ensure_utc(date_to))

    def counted() -> Any:
        stmt = select(func.count()).select_from(Report)
        return stmt.where(*filters) if filters else stmt

    total = db.scalar(counted()) or 0

    def grouped(column) -> Any:
        stmt = select(column, func.count()).group_by(column)
        if filters:
            stmt = stmt.where(*filters)
        return db.execute(stmt).all()

    by_status = [KeyCount(key=row[0] or "TIDAK_DIKETAHUI", count=row[1]) for row in grouped(Report.status)]
    by_urgency = [
        KeyCount(key=row[0] or "TIDAK_DIKETAHUI", count=row[1]) for row in grouped(Report.urgency_final)
    ]
    by_category = [
        CategoryStat(category=row[0] or "lainnya", count=row[1]) for row in grouped(Report.category)
    ]

    month_expr = func.strftime("%Y-%m", Report.created_at)
    by_month = [
        KeyCount(key=row[0] or "TIDAK_DIKETAHUI", count=row[1]) for row in grouped(month_expr)
    ]
    by_month.sort(key=lambda item: item.key)

    responded_stmt = select(Report).where(Report.status != "MENUNGGU_TIM")
    if filters:
        responded_stmt = responded_stmt.where(*filters)
    responded = db.scalars(responded_stmt).all()
    if responded:
        hours = [_hours_between(r.created_at, r.updated_at) for r in responded]
        avg = round(sum(hours) / len(hours), 2)
    else:
        avg = None

    ai_stmt = select(func.count()).select_from(Report).where(Report.ai_failed.is_(True))
    if filters:
        ai_stmt = ai_stmt.where(*filters)
    ai_failed_count = db.scalar(ai_stmt) or 0

    return StatsOut(
        total_reports=total,
        by_status=by_status,
        by_urgency=by_urgency,
        by_category=by_category,
        by_month=by_month,
        average_first_response_hours=avg,
        ai_failed_count=ai_failed_count,
    )


@router.get("/patterns", response_model=list[PatternOut])
def patterns(
    officer: Officer = Depends(get_current_officer),
    db: Session = Depends(get_db),
) -> list[PatternOut]:
    groups: dict[str, dict[str, Any]] = {}
    for report in db.scalars(select(Report)).all():
        for h in report.perpetrator_hmacs or []:
            if not h:
                continue
            entry = groups.setdefault(h, {"tickets": [], "categories": set()})
            entry["tickets"].append(report.ticket_code)
            entry["categories"].add(report.category)

    result = [
        PatternOut(
            perpetrator_hash=h,
            report_count=len(v["tickets"]),
            ticket_codes=v["tickets"],
            categories=sorted(v["categories"]),
        )
        for h, v in groups.items()
        if len(v["tickets"]) >= 2
    ]
    result.sort(key=lambda p: p.report_count, reverse=True)
    return result


# ------------------------------------------------------------------ Audit & petugas


@router.get("/audit-logs", response_model=list[AuditLogOut])
def audit_logs(
    officer: Officer = Depends(require_role("ADMIN")),
    db: Session = Depends(get_db),
    limit: int = Query(200, ge=1, le=1000),
) -> list[AuditLogOut]:
    rows = db.scalars(select(AuditLog).order_by(AuditLog.created_at.desc()).limit(limit)).all()
    return [AuditLogOut.model_validate(r) for r in rows]


@router.get("/officers", response_model=list[OfficerOut])
def list_officers(
    officer: Officer = Depends(get_current_officer),
    db: Session = Depends(get_db),
) -> list[OfficerOut]:
    rows = db.scalars(select(Officer).where(Officer.is_active.is_(True)).order_by(Officer.username)).all()
    return [OfficerOut.model_validate(o) for o in rows]


# ------------------------------------------------------------------ Notifikasi


@router.get("/notifications")
def notifications(
    officer: Officer = Depends(get_current_officer),
    db: Session = Depends(get_db),
) -> dict[str, Any]:
    """Notifikasi ringan: balasan pelapor yang belum dibaca + laporan baru (menunggu tim)."""
    unread = db.scalars(
        select(FollowUpMessage)
        .where(FollowUpMessage.sender == "PELAPOR", FollowUpMessage.read.is_(False))
        .order_by(FollowUpMessage.created_at.desc())
        .limit(50)
    ).all()
    new_reports = db.scalars(
        select(Report)
        .where(Report.status == "MENUNGGU_TIM")
        .order_by(Report.created_at.desc())
        .limit(50)
    ).all()

    return {
        "unread_count": len(unread),
        "new_count": len(new_reports),
        "unread_messages": [
            {
                "id": m.id,
                "report_id": m.report_id,
                "ticket_code": getattr(m.report, "ticket_code", None),
                "body": m.body,
                "created_at": m.created_at,
            }
            for m in unread
        ],
        "new_reports": [
            {
                "id": r.id,
                "ticket_code": r.ticket_code,
                "urgency_final": r.urgency_final,
                "category": r.category,
                "summary": r.summary,
                "created_at": r.created_at,
            }
            for r in new_reports
        ],
    }


@router.post("/notifications/read")
def mark_notifications_read(
    officer: Officer = Depends(get_current_officer),
    db: Session = Depends(get_db),
) -> dict[str, bool]:
    """Tandai semua balasan pelapor sebagai sudah dibaca."""
    db.execute(
        update(FollowUpMessage)
        .where(FollowUpMessage.sender == "PELAPOR", FollowUpMessage.read.is_(False))
        .values(read=True)
    )
    db.commit()
    return {"ok": True}


def _require_report(db: Session, report_id: str) -> Report:
    report = db.get(Report, report_id)
    if report is None:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Laporan tidak ditemukan.")
    return report
