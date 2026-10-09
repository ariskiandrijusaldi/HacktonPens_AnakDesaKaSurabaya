"""Model tabel SQLAlchemy 2.x.

Catatan keamanan:
- `original_text_enc` dan `contact_enc` disimpan sebagai ciphertext Fernet (BLOB).
- `perpetrator_hmacs` menyimpan HMAC-SHA256 dari nama terlapor, BUKAN teks asli.
- Tidak ada kolom IP / user-agent / header identitas pelapor di mana pun.
"""

from __future__ import annotations

import uuid
from datetime import datetime, timezone

from sqlalchemy import (
    JSON,
    Boolean,
    DateTime,
    ForeignKey,
    Integer,
    LargeBinary,
    String,
    Text,
)
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.database import Base


def new_id() -> str:
    return str(uuid.uuid4())


def utcnow() -> datetime:
    return datetime.now(timezone.utc)


class Report(Base):
    __tablename__ = "reports"

    id: Mapped[str] = mapped_column(String(36), primary_key=True, default=new_id)
    ticket_code: Mapped[str] = mapped_column(String(32), unique=True, index=True, nullable=False)
    created_at: Mapped[datetime] = mapped_column(
        DateTime(timezone=True), default=utcnow, index=True, nullable=False
    )
    updated_at: Mapped[datetime] = mapped_column(
        DateTime(timezone=True), default=utcnow, onupdate=utcnow, nullable=False
    )

    # Data sensitif (terenkripsi)
    original_text_enc: Mapped[bytes] = mapped_column(LargeBinary, nullable=False)
    contact_enc: Mapped[bytes | None] = mapped_column(LargeBinary, nullable=True)

    # Data tersensor untuk dasbor
    sanitized_text: Mapped[str] = mapped_column(Text, nullable=False)
    summary: Mapped[str] = mapped_column(Text, default="", nullable=False)
    category: Mapped[str] = mapped_column(String(64), default="lainnya", index=True, nullable=False)
    # Lokasi kejadian (lokasi umum yang diisi pelapor, mis. "Gedung B Fakultas Teknik").
    location: Mapped[str] = mapped_column(String(200), default="", nullable=False)

    urgency_ai: Mapped[str] = mapped_column(String(16), default="KUNING", nullable=False)
    urgency_final: Mapped[str] = mapped_column(String(16), default="KUNING", index=True, nullable=False)
    urgency_rationale: Mapped[str] = mapped_column(Text, default="", nullable=False)

    victim_wish: Mapped[str] = mapped_column(String(32), nullable=False)
    status: Mapped[str] = mapped_column(String(32), default="MENUNGGU_TIM", index=True, nullable=False)
    assigned_to: Mapped[str | None] = mapped_column(
        String(36), ForeignKey("officers.id"), nullable=True
    )

    ai_failed: Mapped[bool] = mapped_column(Boolean, default=False, nullable=False)

    # Daftar HMAC nama terlapor (untuk deteksi pola). Bukan teks asli.
    perpetrator_hmacs: Mapped[list] = mapped_column(JSON, default=list, nullable=False)

    attachments: Mapped[list["Attachment"]] = relationship(
        back_populates="report", cascade="all, delete-orphan"
    )
    notes: Mapped[list["InternalNote"]] = relationship(
        back_populates="report", cascade="all, delete-orphan"
    )
    messages: Mapped[list["FollowUpMessage"]] = relationship(
        back_populates="report", cascade="all, delete-orphan"
    )
    assignee: Mapped["Officer | None"] = relationship(foreign_keys=[assigned_to])


class Attachment(Base):
    __tablename__ = "attachments"

    id: Mapped[str] = mapped_column(String(36), primary_key=True, default=new_id)
    report_id: Mapped[str] = mapped_column(
        String(36), ForeignKey("reports.id"), nullable=False, index=True
    )
    filename_random: Mapped[str] = mapped_column(String(128), nullable=False)
    mime: Mapped[str] = mapped_column(String(64), nullable=False)
    size: Mapped[int] = mapped_column(Integer, nullable=False)
    sha256: Mapped[str] = mapped_column(String(64), nullable=False)
    path: Mapped[str] = mapped_column(String(512), nullable=False)

    report: Mapped["Report"] = relationship(back_populates="attachments")


class Officer(Base):
    __tablename__ = "officers"

    id: Mapped[str] = mapped_column(String(36), primary_key=True, default=new_id)
    username: Mapped[str] = mapped_column(String(64), unique=True, index=True, nullable=False)
    password_hash: Mapped[str] = mapped_column(String(255), nullable=False)
    role: Mapped[str] = mapped_column(String(16), default="ANGGOTA", nullable=False)
    is_active: Mapped[bool] = mapped_column(Boolean, default=True, nullable=False)
    totp_secret: Mapped[str | None] = mapped_column(String(64), nullable=True)
    created_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), default=utcnow, nullable=False)


class InternalNote(Base):
    __tablename__ = "internal_notes"

    id: Mapped[str] = mapped_column(String(36), primary_key=True, default=new_id)
    report_id: Mapped[str] = mapped_column(
        String(36), ForeignKey("reports.id"), nullable=False, index=True
    )
    officer_id: Mapped[str] = mapped_column(String(36), ForeignKey("officers.id"), nullable=False)
    note: Mapped[str] = mapped_column(Text, nullable=False)
    created_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), default=utcnow, nullable=False)

    report: Mapped["Report"] = relationship(back_populates="notes")


class FollowUpMessage(Base):
    __tablename__ = "follow_up_messages"

    id: Mapped[str] = mapped_column(String(36), primary_key=True, default=new_id)
    report_id: Mapped[str] = mapped_column(
        String(36), ForeignKey("reports.id"), nullable=False, index=True
    )
    sender: Mapped[str] = mapped_column(String(16), nullable=False)  # SATGAS | PELAPOR
    body: Mapped[str] = mapped_column(Text, nullable=False)
    created_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), default=utcnow, nullable=False)
    read: Mapped[bool] = mapped_column(Boolean, default=False, nullable=False)

    report: Mapped["Report"] = relationship(back_populates="messages")


class AuditLog(Base):
    __tablename__ = "audit_logs"

    id: Mapped[str] = mapped_column(String(36), primary_key=True, default=new_id)
    officer_id: Mapped[str | None] = mapped_column(
        String(36), ForeignKey("officers.id"), nullable=True
    )
    action: Mapped[str] = mapped_column(String(64), nullable=False, index=True)
    report_id: Mapped[str | None] = mapped_column(String(36), nullable=True)
    detail: Mapped[dict] = mapped_column(JSON, default=dict, nullable=False)
    created_at: Mapped[datetime] = mapped_column(DateTime(timezone=True), default=utcnow, nullable=False)
