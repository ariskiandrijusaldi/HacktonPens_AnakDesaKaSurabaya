"""Engine, session, dan Base SQLAlchemy 2.x.

Default SQLite (single file). Ganti ke Postgres/Supabase cukup lewat DATABASE_URL.
"""

from __future__ import annotations

from collections.abc import Generator

from sqlalchemy import create_engine
from sqlalchemy.orm import DeclarativeBase, Session, sessionmaker

from app.config import get_settings

settings = get_settings()

_connect_args: dict = {}
if settings.database_url.startswith("sqlite"):
    # SQLite + FastAPI multi-thread butuh ini agar session bisa dipakai lintas thread.
    _connect_args = {"check_same_thread": False}

engine = create_engine(
    settings.database_url,
    connect_args=_connect_args,
    pool_pre_ping=True,
    future=True,
)

SessionLocal = sessionmaker(
    bind=engine,
    autoflush=False,
    autocommit=False,
    expire_on_commit=False,
    class_=Session,
)


class Base(DeclarativeBase):
    """Base deklaratif untuk semua model."""


def get_db() -> Generator[Session, None, None]:
    """Dependency FastAPI: berikan sesi DB lalu tutup setelahnya."""
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()
