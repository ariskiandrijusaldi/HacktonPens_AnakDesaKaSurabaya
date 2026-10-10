"""Konfigurasi pytest.

PENTING: environment test disetel SEBELUM modul `app` diimpor, karena `Settings`
di-cache (`lru_cache`) dan engine SQLAlchemy dibuat saat impor.
"""

from __future__ import annotations

import os
import tempfile
from pathlib import Path

from cryptography.fernet import Fernet

_TMP_DB = Path(tempfile.gettempdir()) / "ppks_test.db"
if _TMP_DB.exists():
    _TMP_DB.unlink()

_TMP_UPLOADS = Path(tempfile.gettempdir()) / "ppks_test_uploads"

os.environ.update(
    {
        "DATABASE_URL": f"sqlite:///{_TMP_DB.as_posix()}",
        "ENCRYPTION_KEY": Fernet.generate_key().decode(),
        "HMAC_KEY": "test-hmac-key",
        "JWT_SECRET": "test-jwt-secret",
        "GEMINI_API_KEY": "",  # pakai fallback lokal agar tes deterministik
        "OVERRIDE_PIN": "123456",
        "OVERRIDE_MAX_ATTEMPTS": "3",
        "OVERRIDE_LOCKOUT_MINUTES": "15",
        "ADMIN_USERNAME": "admin",
        "ADMIN_PASSWORD": "adminpass",
        "MEMBER_USERNAME": "anggota",
        "MEMBER_PASSWORD": "memberpass",
        "UPLOAD_DIR": _TMP_UPLOADS.as_posix(),
        "ENVIRONMENT": "testing",
        "RATE_LIMIT_LOGIN": "10000/minute",
        "RATE_LIMIT_REPORT": "10000/minute",
        "RATE_LIMIT_TICKET": "10000/minute",
        "RATE_LIMIT_MESSAGE": "10000/minute",
    }
)

import pytest  # noqa: E402
from fastapi.testclient import TestClient  # noqa: E402


@pytest.fixture(scope="session")
def client():
    from app.main import app

    with TestClient(app) as test_client:
        yield test_client


def _login(client: TestClient, username: str, password: str) -> str:
    resp = client.post("/api/auth/login", json={"username": username, "password": password})
    assert resp.status_code == 200, resp.text
    return resp.json()["access_token"]


@pytest.fixture
def admin_token(client: TestClient) -> str:
    return _login(client, "admin", "adminpass")


@pytest.fixture
def member_token(client: TestClient) -> str:
    return _login(client, "anggota", "memberpass")


@pytest.fixture
def admin_headers(admin_token: str) -> dict:
    return {"Authorization": f"Bearer {admin_token}"}


@pytest.fixture
def member_headers(member_token: str) -> dict:
    return {"Authorization": f"Bearer {member_token}"}


@pytest.fixture
def mock_gemini(monkeypatch):
    """Ganti panggilan Gemini dengan hasil tetap (tanpa jaringan)."""
    from app.config import get_settings
    from app.schemas import AIResult
    from app.services import ai

    # Isi API key palsu agar `triage()` tidak langsung memakai fallback lokal.
    monkeypatch.setattr(get_settings(), "gemini_api_key", "test-key")

    result = AIResult(
        sanitized_text="[Korban-1] melaporkan perbuatan [Terlapor-1].",
        summary="Ringkasan tersensor untuk pengujian.",
        category="pelecehan verbal",
        urgency="MERAH",
        rationale="Terdapat ancaman langsung terhadap korban.",
        perpetrator_names=["Budi Santoso"],
    )

    def _fake(text: str) -> AIResult:
        return result.model_copy(deep=True)

    monkeypatch.setattr(ai, "_call_gemini", _fake)
    return result


@pytest.fixture
def create_report(client: TestClient, mock_gemini):
    def _create(
        text: str = "Saya mengalami pelecehan dan merasa terancam oleh seseorang di kampus.",
        victim_wish: str = "KONSULTASI",
        contact: str | None = None,
        location: str | None = None,
    ):
        data = {"text": text, "victim_wish": victim_wish, "consent": "true"}
        if contact is not None:
            data["contact"] = contact
        if location is not None:
            data["location"] = location
        return client.post("/api/reports", data=data)

    return _create
