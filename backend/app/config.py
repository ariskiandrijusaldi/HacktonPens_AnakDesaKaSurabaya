"""Konfigurasi aplikasi berbasis environment (.env) via pydantic-settings.

Semua rahasia dibaca dari environment. Jangan pernah hardcode secret di kode.
`get_settings()` di-cache agar .env hanya dibaca sekali per proses.
"""

from __future__ import annotations

from functools import lru_cache
from pathlib import Path

from pydantic_settings import BaseSettings, SettingsConfigDict

INSECURE_JWT_SECRET = "insecure-dev-secret-change-me"


class Settings(BaseSettings):
    model_config = SettingsConfigDict(
        env_file=".env",
        env_file_encoding="utf-8",
        case_sensitive=False,
        extra="ignore",
    )

    # Aplikasi
    app_name: str = "Agen Satgas PPKS Kampus"
    environment: str = "development"
    debug: bool = False

    # Database
    database_url: str = "sqlite:///./ppks.db"

    # Kunci keamanan
    encryption_key: str = ""
    hmac_key: str = ""
    jwt_secret: str = INSECURE_JWT_SECRET
    jwt_algorithm: str = "HS256"
    access_token_expire_minutes: int = 30

    # Emergency override
    override_pin: str = ""
    override_pin_hash: str = ""
    override_max_attempts: int = 3
    override_lockout_minutes: int = 15

    # Gemini
    gemini_api_key: str = ""
    gemini_model: str = "gemini-2.0-flash"
    gemini_timeout_seconds: int = 20

    # Telegram
    telegram_bot_token: str = ""
    telegram_chat_id: str = ""
    dashboard_url: str = "http://localhost:5173"

    # CORS (dipisah koma)
    cors_origins: str = "http://localhost:5173,http://localhost:3000"

    # Rate limit (in-memory, IP tidak pernah disimpan ke DB)
    rate_limit_login: str = "5/minute"
    rate_limit_report: str = "10/minute"
    rate_limit_ticket: str = "10/minute"
    rate_limit_message: str = "10/minute"

    # Lampiran
    upload_dir: str = "./storage/attachments"
    max_file_size_mb: int = 5
    max_files_per_report: int = 3

    # Seed petugas
    admin_username: str = "admin"
    admin_password: str = ""
    member_username: str = "anggota"
    member_password: str = ""

    # Sumber daya bantuan
    help_resources_file: str = "./app/help_resources.json"
    procedures_file: str = "./app/procedures.json"
    testimonials_file: str = "./app/testimonials.json"
    profile_file: str = "./app/profile.json"

    @property
    def cors_origin_list(self) -> list[str]:
        return [o.strip() for o in self.cors_origins.split(",") if o.strip()]

    @property
    def max_file_size_bytes(self) -> int:
        return self.max_file_size_mb * 1024 * 1024

    @property
    def is_production(self) -> bool:
        return self.environment.lower() in {"production", "prod"}

    @property
    def insecure_secret_problems(self) -> list[str]:
        """Daftar secret yang belum aman (kosong atau masih default)."""
        problems: list[str] = []
        if not self.jwt_secret or self.jwt_secret == INSECURE_JWT_SECRET:
            problems.append("JWT_SECRET")
        if not self.encryption_key:
            problems.append("ENCRYPTION_KEY")
        if not self.hmac_key:
            problems.append("HMAC_KEY")
        return problems

    @property
    def base_dir(self) -> Path:
        """Direktori `backend/` (folder induk paket `app`)."""
        return Path(__file__).resolve().parent.parent

    @property
    def help_resources_path(self) -> Path:
        """Path file sumber daya bantuan, relatif ke `backend/` bila bukan absolut."""
        p = Path(self.help_resources_file)
        return p if p.is_absolute() else self.base_dir / p

    @property
    def procedures_path(self) -> Path:
        """Path file prosedur pelaporan, relatif ke `backend/` bila bukan absolut."""
        p = Path(self.procedures_file)
        return p if p.is_absolute() else self.base_dir / p

    @property
    def testimonials_path(self) -> Path:
        """Path file testimoni, relatif ke `backend/` bila bukan absolut."""
        p = Path(self.testimonials_file)
        return p if p.is_absolute() else self.base_dir / p

    @property
    def profile_path(self) -> Path:
        """Path file profil Satgas, relatif ke `backend/` bila bukan absolut."""
        p = Path(self.profile_file)
        return p if p.is_absolute() else self.base_dir / p

    @property
    def upload_dir_path(self) -> Path:
        """Direktori lampiran, relatif ke `backend/` bila bukan absolut (tak bergantung CWD)."""
        p = Path(self.upload_dir)
        return p if p.is_absolute() else self.base_dir / p


@lru_cache
def get_settings() -> Settings:
    return Settings()
