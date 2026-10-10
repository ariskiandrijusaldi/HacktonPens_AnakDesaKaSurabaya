"""FastAPI dependencies: sesi DB dan autentikasi/otorisasi petugas."""

from __future__ import annotations

from collections.abc import Callable

from fastapi import Depends, HTTPException, status
from fastapi.security import OAuth2PasswordBearer
from sqlalchemy.orm import Session

from app.database import get_db
from app.models import Officer
from app.security import decode_access_token

oauth2_scheme = OAuth2PasswordBearer(tokenUrl="/api/auth/login", auto_error=False)


def _unauthorized(detail: str = "Token tidak valid atau sudah kedaluwarsa.") -> HTTPException:
    return HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail=detail,
        headers={"WWW-Authenticate": "Bearer"},
    )


def get_current_officer(
    token: str | None = Depends(oauth2_scheme),
    db: Session = Depends(get_db),
) -> Officer:
    if not token:
        raise _unauthorized("Autentikasi diperlukan.")
    payload = decode_access_token(token)
    if not payload or not payload.get("sub"):
        raise _unauthorized()

    officer = db.get(Officer, payload["sub"])
    if officer is None or not officer.is_active:
        raise _unauthorized("Akun petugas tidak ditemukan atau tidak aktif.")
    return officer


def require_role(*roles: str) -> Callable[[Officer], Officer]:
    """Dependency factory: batasi endpoint ke role tertentu."""

    def _checker(officer: Officer = Depends(get_current_officer)) -> Officer:
        if officer.role not in roles:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail="Anda tidak memiliki hak akses untuk aksi ini.",
            )
        return officer

    return _checker
