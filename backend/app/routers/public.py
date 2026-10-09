"""Endpoint publik (pelapor). Tidak menyimpan IP/User-Agent, tidak log body."""

from __future__ import annotations

import json
import logging
from typing import Annotated

from fastapi import (
    APIRouter,
    BackgroundTasks,
    Depends,
    File,
    Form,
    HTTPException,
    Query,
    Request,
    UploadFile,
    status,
)
from sqlalchemy import select
from sqlalchemy.exc import IntegrityError
from sqlalchemy.orm import Session

from app.config import get_settings
from app.database import get_db
from app.limiter import limiter
from app.models import FollowUpMessage, Report, utcnow
from app.schemas import (
    HelpResource,
    ProceduresOut,
    ProfileOut,
    PublicMessageCreate,
    PublicReportCreated,
    TestimonialOut,
    TestimonialsOut,
    TicketInfoOut,
    TicketMessageOut,
    VictimWish,
)
from app.security import encrypt_text, generate_unique_ticket_code, hmac_perpetrator_name
from app.services import ai, files as files_service, notifier, prefilter

logger = logging.getLogger(__name__)
router = APIRouter(prefix="/api", tags=["Publik"])
settings = get_settings()


@router.post("/reports", response_model=PublicReportCreated, status_code=status.HTTP_201_CREATED)
@limiter.limit(settings.rate_limit_report)
def create_report(
    request: Request,
    background_tasks: BackgroundTasks,
    text: Annotated[str, Form(min_length=20, max_length=10000)],
    victim_wish: Annotated[str, Form()],
    consent: Annotated[bool, Form()],
    contact: Annotated[str | None, Form(max_length=255)] = None,
    location: Annotated[str | None, Form(max_length=200)] = None,
    files: Annotated[list[UploadFile] | None, File()] = None,
    db: Session = Depends(get_db),
) -> PublicReportCreated:
    if not consent:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Persetujuan pemrosesan laporan wajib diberikan.",
        )
    try:
        wish = VictimWish(victim_wish)
    except ValueError as exc:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Pilihan keinginan korban tidak valid.",
        ) from exc

    # 1) Prefilter regex lokal -> 2) triase Gemini.
    prefiltered = prefilter.redact(text)
    result, ai_failed = ai.triage(prefiltered)

    contact_value = contact.strip() if contact and contact.strip() else None
    # Lokasi diisi pelapor (lokasi umum). Bersihkan PII yang mungkin ikut tertulis.
    location_value = prefilter.redact(location.strip()) if location and location.strip() else ""
    hmacs = sorted(
        {h for name in result.perpetrator_names if (h := hmac_perpetrator_name(name))}
    )
    # Enkripsi sekali; dapat dipakai ulang saat retry ticket_code.
    original_enc = encrypt_text(text)
    contact_enc = encrypt_text(contact_value) if contact_value else None

    # Cek tabrakan tiket + tangani race kondisi via unique index saat flush.
    report: Report | None = None
    for attempt in range(5):
        code = generate_unique_ticket_code(
            lambda c: db.scalar(select(Report.id).where(Report.ticket_code == c)) is not None
        )
        candidate = Report(
            ticket_code=code,
            original_text_enc=original_enc,
            sanitized_text=result.sanitized_text,
            summary=result.summary,
            category=result.category,
            location=location_value,
            urgency_ai=result.urgency.value,
            urgency_final=result.urgency.value,
            urgency_rationale=result.rationale,
            victim_wish=wish.value,
            contact_enc=contact_enc,
            ai_failed=ai_failed,
            perpetrator_hmacs=hmacs,
        )
        db.add(candidate)
        try:
            db.flush()  # dapatkan report.id + cek unique ticket_code
            report = candidate
            break
        except IntegrityError:
            db.rollback()
            if attempt == 4:
                logger.error("Gagal membuat ticket_code unik setelah 5 percobaan.")
                raise HTTPException(
                    status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
                    detail="Server sedang sibuk. Silakan coba lagi.",
                )
    assert report is not None

    attachments: list = []
    try:
        attachments = files_service.process_uploads(files or [], report.id)
        for attachment in attachments:
            db.add(attachment)
        db.commit()
    except HTTPException:
        db.rollback()
        files_service.remove_attachment_files(attachments)
        raise
    except IntegrityError:
        db.rollback()
        files_service.remove_attachment_files(attachments)
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail="Server sedang sibuk. Silakan coba lagi.",
        )
    except Exception:
        db.rollback()
        files_service.remove_attachment_files(attachments)
        raise

    # Notifikasi hanya untuk MERAH; kegagalan tidak menggagalkan laporan.
    if report.urgency_final == "MERAH":
        background_tasks.add_task(
            notifier.send_red_alert, report.ticket_code, report.summary, report.category
        )

    return PublicReportCreated(ticket_code=report.ticket_code)


def _get_report_or_404(db: Session, ticket_code: str) -> Report:
    report = db.scalar(select(Report).where(Report.ticket_code == ticket_code))
    if report is None:
        # Pesan seragam untuk mencegah enumerasi tiket.
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Tiket tidak ditemukan.")
    return report


@router.get("/tickets/{ticket_code}", response_model=TicketInfoOut)
@limiter.limit(settings.rate_limit_ticket)
def get_ticket(request: Request, ticket_code: str, db: Session = Depends(get_db)) -> TicketInfoOut:
    report = _get_report_or_404(db, ticket_code)

    messages = [
        TicketMessageOut.model_validate(m)
        for m in sorted(report.messages, key=lambda m: m.created_at)
        if m.sender == "SATGAS"
    ]
    return TicketInfoOut(
        ticket_code=report.ticket_code,
        status=report.status,
        updated_at=report.updated_at,
        messages=messages,
    )


@router.post("/tickets/{ticket_code}/messages", status_code=status.HTTP_201_CREATED)
@limiter.limit(settings.rate_limit_message)
def post_public_message(
    request: Request,
    ticket_code: str,
    payload: PublicMessageCreate,
    db: Session = Depends(get_db),
) -> dict:
    report = _get_report_or_404(db, ticket_code)
    db.add(
        FollowUpMessage(
            report_id=report.id,
            sender="PELAPOR",
            body=payload.body,
        )
    )
    report.updated_at = utcnow()
    db.commit()
    return {"ok": True}


def _load_config_json(path, detail: str):
    """Muat file konfigurasi JSON. Raise 503 dengan pesan ramah bila gagal."""
    if not path.exists():
        logger.warning("File konfigurasi tidak ditemukan: %s", path)
        raise HTTPException(status_code=status.HTTP_503_SERVICE_UNAVAILABLE, detail=detail)
    try:
        return json.loads(path.read_text(encoding="utf-8"))
    except (json.JSONDecodeError, OSError) as exc:
        logger.warning("Gagal membaca konfigurasi %s: %s", path, exc)
        raise HTTPException(status_code=status.HTTP_503_SERVICE_UNAVAILABLE, detail=detail) from exc


@router.get("/help-resources", response_model=list[HelpResource])
def help_resources() -> list[HelpResource]:
    """Kontak darurat. Konsisten dengan endpoint config lain: 503 bila file tak terbaca."""
    raw = _load_config_json(
        settings.help_resources_path, "Informasi sumber daya bantuan belum tersedia."
    )
    return [HelpResource(**item) for item in raw.values()]


@router.get("/procedures", response_model=ProceduresOut)
def procedures() -> ProceduresOut:
    """Prosedur pelaporan (tahapan, syarat, kanal, komitmen, dasar hukum, FAQ)."""
    raw = _load_config_json(settings.procedures_path, "Informasi prosedur pelaporan belum tersedia.")
    return ProceduresOut(**raw)


@router.get("/testimonials", response_model=TestimonialsOut)
def testimonials(
    page: int = Query(1, ge=1),
    limit: int = Query(10, ge=1, le=50),
) -> TestimonialsOut:
    """Galeri testimoni anonim (terbaru lebih dulu) dengan paginasi."""
    raw = _load_config_json(settings.testimonials_path, "Informasi testimoni belum tersedia.")

    items = sorted(raw.get("items", []), key=lambda item: item.get("date", ""), reverse=True)
    total = len(items)
    start = (page - 1) * limit
    return TestimonialsOut(
        title=raw.get("title", "Testimoni"),
        intro=raw.get("intro", ""),
        items=[TestimonialOut(**item) for item in items[start : start + limit]],
        total=total,
        page=page,
        limit=limit,
    )


@router.get("/profile", response_model=ProfileOut)
def profile() -> ProfileOut:
    """Profil Satgas PPKS (sekilas, dasar hukum, tugas, visi-misi, struktur, kontak)."""
    raw = _load_config_json(settings.profile_path, "Informasi profil Satgas belum tersedia.")
    return ProfileOut(**raw)
