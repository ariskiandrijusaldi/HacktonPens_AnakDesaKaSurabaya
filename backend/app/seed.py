"""Seed data: petugas default dan laporan demo untuk kebutuhan presentasi."""

from __future__ import annotations

import logging
import secrets
import string
from datetime import datetime, timedelta, timezone

from sqlalchemy import func, select
from sqlalchemy.orm import Session

from app.config import get_settings
from app.models import FollowUpMessage, Officer, Report
from app.security import encrypt_text, generate_unique_ticket_code, hmac_perpetrator_name, hash_password

logger = logging.getLogger(__name__)
settings = get_settings()


def _random_password(length: int = 12) -> str:
    alphabet = string.ascii_letters + string.digits
    return "".join(secrets.choice(alphabet) for _ in range(length))


def seed_officers(db: Session) -> None:
    """Buat 1 ADMIN + 1 ANGGOTA jika tabel Officer masih kosong."""
    count = db.scalar(select(func.count()).select_from(Officer)) or 0
    if count > 0:
        return

    admin_password = settings.admin_password or _random_password()
    member_password = settings.member_password or _random_password()

    db.add_all(
        [
            Officer(
                username=settings.admin_username,
                password_hash=hash_password(admin_password),
                role="ADMIN",
            ),
            Officer(
                username=settings.member_username,
                password_hash=hash_password(member_password),
                role="ANGGOTA",
            ),
        ]
    )
    db.commit()

    # Password acak hanya ditampilkan sekali agar bisa dicatat operator.
    if not settings.admin_password:
        logger.warning("SEED: admin '%s' password = %s", settings.admin_username, admin_password)
    if not settings.member_password:
        logger.warning("SEED: anggota '%s' password = %s", settings.member_username, member_password)


_DEMO_REPORTS: list[dict] = [
    {
        "original": (
            "Saya mahasiswa semester 3. Dosen berinisial Budi Santoso memanggil saya ke "
            "ruangannya lalu memaksa memegang tangan saya dan mengancam nilai saya. "
            "Saya takut dan tidak tahu harus bagaimana. Nomor saya 081234567890."
        ),
        "sanitized": (
            "Saya mahasiswa semester 3. Dosen berinisial [Terlapor-1] memanggil saya ke "
            "ruangannya lalu memaksa memegang tangan saya dan mengancam nilai saya. "
            "Saya takut dan tidak tahu harus bagaimana. Nomor saya [NO-HP]."
        ),
        "summary": "Dosen memaksa kontak fisik dan mengancam nilai korban.",
        "category": "pelecehan fisik",
        "urgency": "MERAH",
        "rationale": "Ada ancaman dan kekerasan fisik. Korban berada dalam tekanan langsung.",
        "wish": "PENDAMPINGAN",
        "status": "MENUNGGU_TIM",
        "perps": ["Budi Santoso"],
        "hours_ago": 30,
    },
    {
        "original": (
            "Ketua organisasi Budi Santoso terus mengirim pesan bernuansa seksual setiap hari "
            "dan mengintimidasi saya agar tidak melapor. Email saya ani@example.com."
        ),
        "sanitized": (
            "Ketua organisasi [Terlapor-1] terus mengirim pesan bernuansa seksual setiap hari "
            "dan mengintimidasi saya agar tidak melapor. Email saya [EMAIL]."
        ),
        "summary": "Intimidasi berulang dan pesan bernuansa seksual dari ketua organisasi.",
        "category": "pelecehan verbal",
        "urgency": "KUNING",
        "rationale": "Intimidasi berulang dan relasi kuasa menimbulkan tekanan psikologis berat.",
        "wish": "KONSULTASI",
        "status": "SEDANG_DIDAMPINGI",
        "perps": ["Budi Santoso"],
        "hours_ago": 20,
    },
    {
        "original": (
            "Teman sekelas saya menyebarkan lelucon seksual yang membuat saya malu di depan kelas. "
            "Saya ingin bertanya langkah yang bisa saya tempuh."
        ),
        "sanitized": (
            "Teman sekelas saya menyebarkan lelucon seksual yang membuat saya malu di depan kelas. "
            "Saya ingin bertanya langkah yang bisa saya tempuh."
        ),
        "summary": "Lelucon seksual di kelas yang membuat korban malu.",
        "category": "pelecehan verbal",
        "urgency": "HIJAU",
        "rationale": "Tidak ada bahaya langsung; korban mencari konsultasi awal.",
        "wish": "KONSULTASI",
        "status": "SELESAI",
        "perps": [],
        "hours_ago": 72,
    },
    {
        "original": (
            "Saya dipaksa masuk ke mobil oleh dua orang dan dibawa keliling. Saya sempat disekap "
            "selama beberapa jam. Tolong bantu saya, ini darurat."
        ),
        "sanitized": (
            "Saya dipaksa masuk ke mobil oleh dua orang dan dibawa keliling. Saya sempat disekap "
            "selama beberapa jam. Tolong bantu saya, ini darurat."
        ),
        "summary": "Korban disekap dan dipaksa masuk mobil; situasi darurat.",
        "category": "ancaman",
        "urgency": "MERAH",
        "rationale": "Penyekapan dan ancaman nyawa: korban dalam bahaya langsung.",
        "wish": "PROSES_FORMAL",
        "status": "SEDANG_DIDAMPINGI",
        "perps": ["Budi Santoso", "Andi Pratama"],
        "hours_ago": 5,
    },
    {
        "original": (
            "Atasan magang saya mengirim foto tidak senonoh lewat chat dan meminta hal serupa. "
            "Saya takut kehilangan kesempatan magang. Akun IG saya @pelapor_rahasia."
        ),
        "sanitized": (
            "Atasan magang saya mengirim foto tidak senonoh lewat chat dan meminta hal serupa. "
            "Saya takut kehilangan kesempatan magang. Akun IG saya [AKUN]."
        ),
        "summary": "Atasan magang mengirim konten tidak senonoh dan meminta imbalan serupa.",
        "category": "pelecehan verbal",
        "urgency": "KUNING",
        "rationale": "Ada relasi kuasa dan tekanan psikologis; belum ada bahaya fisik langsung.",
        "wish": "PENDAMPINGAN",
        "status": "MENUNGGU_TIM",
        "perps": ["Dedi Gunawan"],
        "hours_ago": 12,
    },
    {
        "original": (
            "Saya hanya ingin tahu prosedur pelaporan kekerasan seksual di kampus. Belum terjadi "
            "apa-apa pada saya, tetapi saya ingin siap."
        ),
        "sanitized": (
            "Saya hanya ingin tahu prosedur pelaporan kekerasan seksual di kampus. Belum terjadi "
            "apa-apa pada saya, tetapi saya ingin siap."
        ),
        "summary": "Permintaan informasi prosedur pelaporan.",
        "category": "lainnya",
        "urgency": "HIJAU",
        "rationale": "Konsultasi awal tanpa indikasi bahaya.",
        "wish": "BELUM_TAHU",
        "status": "SELESAI",
        "perps": [],
        "hours_ago": 100,
    },
    {
        "original": (
            "Seorang senior bernama Andi Pratama mengancam akan menyebarkan foto saya jika saya "
            "tidak menuruti permintaannya. Saya sangat ketakutan."
        ),
        "sanitized": (
            "Seorang senior bernama [Terlapor-1] mengancam akan menyebarkan foto saya jika saya "
            "tidak menuruti permintaannya. Saya sangat ketakutan."
        ),
        "summary": "Pemerasan seksual dengan ancaman menyebarkan foto korban.",
        "category": "ancaman",
        "urgency": "MERAH",
        "rationale": "Ancaman penyebaran foto dan bahaya langsung terhadap korban.",
        "wish": "PROSES_FORMAL",
        "status": "MENUNGGU_TIM",
        "perps": ["Andi Pratama"],
        "hours_ago": 2,
    },
    {
        "original": (
            "Rekan kerja saya sering menyentuh saya tanpa izin saat jam kerja meski sudah saya "
            "tolak. Saya minta pendampingan untuk melapor ke atasan."
        ),
        "sanitized": (
            "Rekan kerja saya sering menyentuh saya tanpa izin saat jam kerja meski sudah saya "
            "tolak. Saya minta pendampingan untuk melapor ke atasan."
        ),
        "summary": "Sentuhan tanpa izin berulang di tempat kerja meski sudah ditolak.",
        "category": "pelecehan fisik",
        "urgency": "KUNING",
        "rationale": "Kekerasan fisik sudah terjadi dan berulang; perlu pendampingan.",
        "wish": "PENDAMPINGAN",
        "status": "SEDANG_DIDAMPINGI",
        "perps": ["Rina Melati"],
        "hours_ago": 48,
    },
]


def seed_demo_reports(db: Session) -> int:
    """Masukkan laporan fiktif. Kembalikan jumlah yang dibuat.

    Termasuk satu terlapor berulang (Budi Santoso) agar fitur pola langsung terlihat.
    """
    created = 0
    now = datetime.now(timezone.utc)

    locations = [
        "Gedung A Fakultas Teknik",
        "Sekretariat organisasi, Gedung B",
        "Ruang kelas, Gedung C",
        "Area parkir kampus",
        "Kantor mitra magang, lantai 3",
        "Layanan informasi akademik",
        "Asrama mahasiswa, Blok D",
        "Ruang kerja, Gedung E",
    ]

    for item, location in zip(_DEMO_REPORTS, locations):
        code = generate_unique_ticket_code(
            lambda c: db.scalar(select(Report.id).where(Report.ticket_code == c)) is not None
        )
        created_at = now - timedelta(hours=float(item["hours_ago"]))
        hmacs = sorted({h for n in item["perps"] if (h := hmac_perpetrator_name(n))})

        report = Report(
            ticket_code=code,
            created_at=created_at,
            updated_at=created_at,
            original_text_enc=encrypt_text(item["original"]),
            sanitized_text=item["sanitized"],
            summary=item["summary"],
            category=item["category"],
            location=location,
            urgency_ai=item["urgency"],
            urgency_final=item["urgency"],
            urgency_rationale=item["rationale"],
            victim_wish=item["wish"],
            status=item["status"],
            contact_enc=encrypt_text("0812-0000-0000") if item["wish"] != "BELUM_TAHU" else None,
            perpetrator_hmacs=hmacs,
        )
        db.add(report)
        created += 1

    db.commit()

    # Beberapa pesan lanjutan pada satu tiket demo.
    first = db.scalar(select(Report).order_by(Report.created_at.asc()))
    if first is not None:
        db.add_all(
            [
                FollowUpMessage(
                    report_id=first.id,
                    sender="SATGAS",
                    body="Terima kasih sudah melapor. Apakah Anda aman saat ini?",
                ),
                FollowUpMessage(
                    report_id=first.id,
                    sender="PELAPOR",
                    body="Saya sedang di tempat aman, terima kasih.",
                ),
            ]
        )
        db.commit()

    return created
