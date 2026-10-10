"""Utilitas keamanan: enkripsi Fernet, hash password, JWT, generator tiket,
HMAC terlapor, dan guard PIN override.

Prinsip:
- `cryptography` (Fernet) untuk data yang HARUS bisa dibaca kembali.
- `hashlib.sha256` untuk integritas file, `bcrypt` untuk password, `python-jose`
  untuk JWT. Jangan menyebut hashing sebagai enkripsi.
"""

from __future__ import annotations

import hashlib
import hmac
import secrets
import threading
from datetime import datetime, timedelta, timezone
from functools import lru_cache

from cryptography.fernet import Fernet, InvalidToken
from jose import JWTError, jwt
from passlib.context import CryptContext

from app.config import get_settings

settings = get_settings()

# Ticket code: angka acak saja (mudah dibaca pelapor, tetap sulit ditebak).
_TICKET_DIGITS = "0123456789"
_TICKET_GROUP_LEN = 6
_TICKET_GROUPS = 2

pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")


# ------------------------------------------------------------------ Fernet


@lru_cache
def get_fernet() -> Fernet:
    if not settings.encryption_key:
        raise RuntimeError(
            "ENCRYPTION_KEY belum diisi. Jalankan `python generate_keys.py` dan "
            "isi ENCRYPTION_KEY di .env."
        )
    try:
        return Fernet(settings.encryption_key.encode())
    except (ValueError, TypeError) as exc:  # key tidak valid
        raise RuntimeError(
            "ENCRYPTION_KEY tidak valid. Harus berupa kunci Fernet (base64 urlsafe 32 byte)."
        ) from exc


def encrypt_text(plaintext: str) -> bytes:
    return get_fernet().encrypt(plaintext.encode("utf-8"))


def decrypt_text(ciphertext: bytes | None) -> str | None:
    if ciphertext is None:
        return None
    try:
        return get_fernet().decrypt(ciphertext).decode("utf-8")
    except InvalidToken as exc:
        raise RuntimeError("Gagal mendekripsi data: kunci tidak cocok atau data rusak.") from exc


# ------------------------------------------------------------------ Password


def hash_password(password: str) -> str:
    return pwd_context.hash(password)


def verify_password(plain: str, hashed: str) -> bool:
    try:
        return pwd_context.verify(plain, hashed)
    except ValueError:
        return False


# ------------------------------------------------------------------ JWT


def create_access_token(officer_id: str, role: str, username: str) -> tuple[str, int]:
    """Kembalikan (token, masa_berlaku_detik)."""
    expire_minutes = settings.access_token_expire_minutes
    expire = datetime.now(timezone.utc) + timedelta(minutes=expire_minutes)
    payload = {
        "sub": officer_id,
        "role": role,
        "username": username,
        "exp": expire,
    }
    token = jwt.encode(payload, settings.jwt_secret, algorithm=settings.jwt_algorithm)
    return token, expire_minutes * 60


def decode_access_token(token: str) -> dict | None:
    try:
        return jwt.decode(token, settings.jwt_secret, algorithms=[settings.jwt_algorithm])
    except JWTError:
        return None


# ------------------------------------------------------------------ Tiket


def generate_ticket_code() -> str:
    """Format `PPKS-XXXXXX-XXXXXX`: 12 digit acak dari `secrets` (numeric-only)."""
    groups = [
        "".join(secrets.choice(_TICKET_DIGITS) for _ in range(_TICKET_GROUP_LEN))
        for _ in range(_TICKET_GROUPS)
    ]
    return "PPKS-" + "-".join(groups)


def generate_unique_ticket_code(exists_fn) -> str:
    """Cek tabrakan; coba ulang hingga 10 kali."""
    for _ in range(10):
        code = generate_ticket_code()
        if not exists_fn(code):
            return code
    raise RuntimeError("Gagal membuat ticket_code unik setelah 10 percobaan.")


# ------------------------------------------------------------------ HMAC terlapor


def hmac_perpetrator_name(name: str) -> str:
    """HMAC-SHA256 nama terlapor dengan kunci rahasia. Tidak menyimpan teks asli."""
    normalized = " ".join(name.strip().lower().split())
    if not normalized:
        return ""
    key = settings.hmac_key.encode("utf-8") or settings.jwt_secret.encode("utf-8")
    return hmac.new(key, normalized.encode("utf-8"), hashlib.sha256).hexdigest()


def sha256_hex(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


# ------------------------------------------------------------------ Override PIN


@lru_cache
def get_override_pin_hash() -> str:
    """Ambil hash PIN. Utamakan OVERRIDE_PIN_HASH; jika kosong, hash OVERRIDE_PIN."""
    if settings.override_pin_hash:
        return settings.override_pin_hash
    if settings.override_pin:
        return hash_password(settings.override_pin)
    return ""


class PinLockedError(Exception):
    """PIN sedang terkunci sementara karena percobaan gagal berulang."""


class OverridePinGuard:
    """Guard in-memory untuk percobaan PIN override.

    Status disimpan per officer_id di memori (cocok untuk single-process hackathon).
    Tidak pernah menulis apa pun ke DB/log terkait PIN.
    """

    def __init__(self, max_attempts: int, lockout_minutes: int) -> None:
        self._max_attempts = max_attempts
        self._lockout = timedelta(minutes=lockout_minutes)
        self._failures: dict[str, int] = {}
        self._locked_until: dict[str, datetime] = {}
        self._lock = threading.Lock()

    def _remaining_lock_seconds(self, officer_id: str) -> int:
        until = self._locked_until.get(officer_id)
        if until and until > datetime.now(timezone.utc):
            return int((until - datetime.now(timezone.utc)).total_seconds()) + 1
        return 0

    def check(self, officer_id: str, pin: str) -> bool:
        """Verifikasi PIN. Raise PinLockedError jika terkunci."""
        with self._lock:
            remaining = self._remaining_lock_seconds(officer_id)
            if remaining > 0:
                raise PinLockedError(f"PIN terkunci. Coba lagi dalam {remaining} detik.")

            pin_hash = get_override_pin_hash()
            if not pin_hash:
                return False

            if verify_password(pin, pin_hash):
                self._failures.pop(officer_id, None)
                self._locked_until.pop(officer_id, None)
                return True

            self._failures[officer_id] = self._failures.get(officer_id, 0) + 1
            if self._failures[officer_id] >= self._max_attempts:
                self._locked_until[officer_id] = datetime.now(timezone.utc) + self._lockout
                self._failures[officer_id] = 0
            return False


override_pin_guard = OverridePinGuard(
    max_attempts=settings.override_max_attempts,
    lockout_minutes=settings.override_lockout_minutes,
)
