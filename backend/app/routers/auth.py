"""Endpoint autentikasi petugas."""

from __future__ import annotations

import logging

import pyotp
from fastapi import APIRouter, Depends, HTTPException, Request, status
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.config import get_settings
from app.database import get_db
from app.limiter import limiter
from app.models import Officer
from app.schemas import LoginRequest, TokenOut
from app.security import create_access_token, verify_password
from app.services import audit

logger = logging.getLogger(__name__)
router = APIRouter(prefix="/api/auth", tags=["Auth"])
settings = get_settings()


@router.post("/login", response_model=TokenOut)
@limiter.limit(settings.rate_limit_login)
def login(payload: LoginRequest, request: Request, db: Session = Depends(get_db)) -> TokenOut:
    officer = db.scalar(select(Officer).where(Officer.username == payload.username))

    # Pesan error seragam (401) agar tidak membocorkan username/akun mana yang ada.
    if (
        officer is None
        or not verify_password(payload.password, officer.password_hash)
        or not officer.is_active
    ):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED, detail="Username atau password salah."
        )

    if officer.totp_secret:
        if not payload.totp_code:
            raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Kode 2FA diperlukan.")
        if not pyotp.TOTP(officer.totp_secret).verify(payload.totp_code, valid_window=1):
            raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Kode 2FA salah.")

    token, expires_in = create_access_token(officer.id, officer.role, officer.username)
    audit.log_action(db, audit.ACTION_LOGIN, officer_id=officer.id)
    db.commit()

    return TokenOut(
        access_token=token,
        expires_in=expires_in,
        role=officer.role,
        username=officer.username,
    )
