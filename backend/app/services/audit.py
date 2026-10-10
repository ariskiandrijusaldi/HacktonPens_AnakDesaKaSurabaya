"""Pencatatan audit log. Semua aksi petugas yang sensitif harus tercatat."""

from __future__ import annotations

from typing import Any

from sqlalchemy.orm import Session

from app.models import AuditLog

# Konstanta aksi agar konsisten
ACTION_LOGIN = "LOGIN"
ACTION_VIEW_REPORT = "LIHAT_LAPORAN"
ACTION_VIEW_LIST = "LIHAT_DAFTAR"
ACTION_CHANGE_STATUS = "UBAH_STATUS"
ACTION_CHANGE_URGENCY = "UBAH_URGENSI"
ACTION_ASSIGN = "TUGASKAN"
ACTION_SEND_MESSAGE = "KIRIM_PESAN"
ACTION_ADD_NOTE = "TAMBAH_CATATAN"
ACTION_OVERRIDE = "OVERRIDE_IDENTITAS"
ACTION_PUBLIC_MESSAGE = "PESAN_PELAPOR"


def log_action(
    db: Session,
    action: str,
    *,
    officer_id: str | None = None,
    report_id: str | None = None,
    detail: dict[str, Any] | None = None,
) -> AuditLog:
    """Tambahkan entri audit ke sesi (belum commit; pemanggil yang commit)."""
    entry = AuditLog(
        officer_id=officer_id,
        action=action,
        report_id=report_id,
        detail=detail or {},
    )
    db.add(entry)
    return entry
