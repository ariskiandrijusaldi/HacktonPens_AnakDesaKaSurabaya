"""Skema Pydantic v2 (request & response).

Enum disimpan sebagai string di DB; gunakan `.value` saat menulis.
"""

from __future__ import annotations

from datetime import datetime
from enum import Enum

from pydantic import BaseModel, ConfigDict, Field


class Urgency(str, Enum):
    MERAH = "MERAH"
    KUNING = "KUNING"
    HIJAU = "HIJAU"


class VictimWish(str, Enum):
    KONSULTASI = "KONSULTASI"
    PENDAMPINGAN = "PENDAMPINGAN"
    PROSES_FORMAL = "PROSES_FORMAL"
    BELUM_TAHU = "BELUM_TAHU"


class ReportStatus(str, Enum):
    MENUNGGU_TIM = "MENUNGGU_TIM"
    SEDANG_DIDAMPINGI = "SEDANG_DIDAMPINGI"
    SELESAI = "SELESAI"


class Role(str, Enum):
    ADMIN = "ADMIN"
    ANGGOTA = "ANGGOTA"


class MessageSender(str, Enum):
    SATGAS = "SATGAS"
    PELAPOR = "PELAPOR"


# ---------------------------------------------------------------- AI internal


class AIResult(BaseModel):
    """Hasil triase AI yang divalidasi. `perpetrator_names` hanya dipakai server
    untuk HMAC lalu dibuang; tidak pernah dikembalikan ke klien."""

    sanitized_text: str
    summary: str = ""
    category: str = "lainnya"
    urgency: Urgency = Urgency.KUNING
    rationale: str = ""
    perpetrator_names: list[str] = Field(default_factory=list)


# ---------------------------------------------------------------- Publik


class PublicMessageCreate(BaseModel):
    body: str = Field(min_length=1, max_length=2000)


class TicketMessageOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    sender: MessageSender
    body: str
    created_at: datetime
    read: bool


class TicketInfoOut(BaseModel):
    ticket_code: str
    status: ReportStatus
    updated_at: datetime
    messages: list[TicketMessageOut] = Field(default_factory=list)


class PublicReportCreated(BaseModel):
    ticket_code: str


class HelpResource(BaseModel):
    name: str
    description: str = ""
    phone: str = ""
    url: str = ""
    hours: str = ""


class ProcedureStep(BaseModel):
    order: int
    title: str
    description: str = ""


class ProcedureChannel(BaseModel):
    name: str
    kind: str = ""
    description: str = ""
    url: str = ""


class ProcedureCommitment(BaseModel):
    label: str
    value: str


class ProcedureFAQ(BaseModel):
    question: str
    answer: str


class ProceduresOut(BaseModel):
    title: str
    intro: str = ""
    steps: list[ProcedureStep] = Field(default_factory=list)
    requirements: list[str] = Field(default_factory=list)
    channels: list[ProcedureChannel] = Field(default_factory=list)
    commitments: list[ProcedureCommitment] = Field(default_factory=list)
    legal_basis: list[str] = Field(default_factory=list)
    faq: list[ProcedureFAQ] = Field(default_factory=list)


class TestimonialOut(BaseModel):
    id: str
    date: str = ""
    author_alias: str = "Anonim"
    title: str
    message: str
    tags: list[str] = Field(default_factory=list)
    media_type: str = "none"
    media_url: str = ""


class TestimonialsOut(BaseModel):
    title: str
    intro: str = ""
    items: list[TestimonialOut] = Field(default_factory=list)
    total: int = 0
    page: int = 1
    limit: int = 10


class ProfileSection(BaseModel):
    title: str
    paragraphs: list[str] = Field(default_factory=list)
    bullets: list[str] = Field(default_factory=list)


class ProfileMember(BaseModel):
    name: str
    role: str
    unit: str = ""


class ProfileContact(BaseModel):
    label: str
    value: str


class ProfileOut(BaseModel):
    title: str
    tagline: str = ""
    sections: list[ProfileSection] = Field(default_factory=list)
    members: list[ProfileMember] = Field(default_factory=list)
    contacts: list[ProfileContact] = Field(default_factory=list)


# ---------------------------------------------------------------- Auth


class LoginRequest(BaseModel):
    username: str = Field(min_length=1, max_length=64)
    password: str = Field(min_length=1, max_length=255)
    totp_code: str | None = Field(default=None, max_length=10)


class TokenOut(BaseModel):
    access_token: str
    token_type: str = "bearer"
    expires_in: int
    role: Role
    username: str


# ---------------------------------------------------------------- Petugas


class AttachmentOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: str
    filename_random: str
    mime: str
    size: int
    sha256: str


class NoteOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: str
    officer_id: str
    note: str
    created_at: datetime


class MessageOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: str
    sender: MessageSender
    body: str
    created_at: datetime
    read: bool


class OfficerOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: str
    username: str
    role: Role
    is_active: bool


class ReportListItem(BaseModel):
    id: str
    ticket_code: str
    summary: str
    category: str
    location: str = ""
    urgency_ai: Urgency
    urgency_final: Urgency
    status: ReportStatus
    victim_wish: VictimWish
    created_at: datetime
    updated_at: datetime
    assigned_to: str | None
    ai_failed: bool
    waiting_hours: float


class ReportListOut(BaseModel):
    items: list[ReportListItem]
    total: int
    page: int
    limit: int


class ReportDetail(ReportListItem):
    sanitized_text: str
    urgency_rationale: str
    contact_available: bool
    attachments: list[AttachmentOut] = Field(default_factory=list)
    notes: list[NoteOut] = Field(default_factory=list)
    messages: list[MessageOut] = Field(default_factory=list)


class StatusUpdate(BaseModel):
    status: ReportStatus


class UrgencyUpdate(BaseModel):
    urgency: Urgency
    reason: str = Field(min_length=3, max_length=1000)


class AssignRequest(BaseModel):
    officer_id: str | None = Field(default=None, max_length=36)


class NoteCreate(BaseModel):
    note: str = Field(min_length=1, max_length=5000)


class OfficerMessageCreate(BaseModel):
    body: str = Field(min_length=1, max_length=2000)


class OverrideRequest(BaseModel):
    pin: str = Field(min_length=1, max_length=64)
    confirm_pin: str = Field(min_length=1, max_length=64)
    reason: str = Field(min_length=20, max_length=2000)


class OverrideResponse(BaseModel):
    report_id: str
    ticket_code: str
    contact: str | None
    original_text: str
    message: str


class AuditLogOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: str
    officer_id: str | None
    action: str
    report_id: str | None
    detail: dict
    created_at: datetime


class CategoryStat(BaseModel):
    category: str
    count: int


class KeyCount(BaseModel):
    key: str
    count: int


class StatsOut(BaseModel):
    total_reports: int
    by_status: list[KeyCount]
    by_urgency: list[KeyCount]
    by_category: list[CategoryStat]
    average_first_response_hours: float | None
    ai_failed_count: int


class PatternOut(BaseModel):
    perpetrator_hash: str
    report_count: int
    ticket_codes: list[str]
    categories: list[str]
