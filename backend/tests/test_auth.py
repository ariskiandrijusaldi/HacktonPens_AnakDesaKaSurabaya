"""Test autentikasi petugas: anti-enumerasi username, akun nonaktif, dan 2FA."""

import pyotp
from fastapi.testclient import TestClient

from app.database import SessionLocal
from app.models import Officer
from app.security import hash_password


def _add_officer(username: str, password: str, *, is_active: bool = True, totp_secret: str | None = None) -> None:
    db = SessionLocal()
    try:
        db.add(
            Officer(
                username=username,
                password_hash=hash_password(password),
                role="ANGGOTA",
                is_active=is_active,
                totp_secret=totp_secret,
            )
        )
        db.commit()
    finally:
        db.close()


def test_inactive_account_returns_uniform_401(client: TestClient):
    _add_officer("nonaktif", "rahasia123", is_active=False)

    inactive = client.post("/api/auth/login", json={"username": "nonaktif", "password": "rahasia123"})
    assert inactive.status_code == 401

    missing = client.post("/api/auth/login", json={"username": "tidak-ada", "password": "x"})
    assert missing.status_code == 401
    # Pesan seragam agar tidak membocorkan keberadaan akun.
    assert inactive.json()["detail"] == missing.json()["detail"]


def test_totp_required_and_verified(client: TestClient):
    secret = pyotp.random_base32()
    _add_officer("petugas2fa", "rahasia123", totp_secret=secret)

    no_code = client.post("/api/auth/login", json={"username": "petugas2fa", "password": "rahasia123"})
    assert no_code.status_code == 401

    wrong_code = client.post(
        "/api/auth/login",
        json={"username": "petugas2fa", "password": "rahasia123", "totp_code": "000000"},
    )
    assert wrong_code.status_code == 401

    ok = client.post(
        "/api/auth/login",
        json={
            "username": "petugas2fa",
            "password": "rahasia123",
            "totp_code": pyotp.TOTP(secret).now(),
        },
    )
    assert ok.status_code == 200, ok.text
    assert ok.json()["access_token"]