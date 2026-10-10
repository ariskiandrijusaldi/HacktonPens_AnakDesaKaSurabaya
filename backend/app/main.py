"""Agen Satgas PPKS Kampus — aplikasi FastAPI.

Inisialisasi app, CORS, header keamanan, rate limiter, router, dan seed startup.
Jalankan: `uvicorn app.main:app --reload`
"""

from __future__ import annotations

import logging
from contextlib import asynccontextmanager

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from slowapi import _rate_limit_exceeded_handler
from slowapi.errors import RateLimitExceeded

from app import models  # noqa: F401  (pastikan model terdaftar sebelum create_all)
from app.config import get_settings
from app.database import Base, SessionLocal, engine
from app.limiter import limiter
from app.routers import auth, officer, public
from app.seed import seed_officers

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)
settings = get_settings()


@asynccontextmanager
async def lifespan(app: FastAPI):
    # Fail-fast: jangan pernah jalan di produksi dengan secret default/kosong.
    if settings.is_production:
        problems = settings.insecure_secret_problems
        if problems:
            raise RuntimeError(
                "Secret produksi belum aman (default/kosong): "
                + ", ".join(problems)
                + ". Jalankan `python generate_keys.py` dan isi .env."
            )

    # Hackathon: create_all cukup. Untuk produksi gunakan Alembic migration.
    Base.metadata.create_all(bind=engine)
    db = SessionLocal()
    try:
        seed_officers(db)
    finally:
        db.close()
    yield


app = FastAPI(
    title=settings.app_name,
    description=(
        "Sistem pelaporan kekerasan seksual anonim dengan triase AI. "
        "Endpoint publik tidak menyimpan IP/User-Agent pelapor."
    ),
    version="1.0.0",
    lifespan=lifespan,
)

app.state.limiter = limiter
app.add_exception_handler(RateLimitExceeded, _rate_limit_exceeded_handler)

app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.cors_origin_list,
    allow_credentials=False,
    allow_methods=["*"],
    allow_headers=["*"],
)


@app.middleware("http")
async def security_headers(request, call_next):
    response = await call_next(request)
    response.headers["X-Content-Type-Options"] = "nosniff"
    response.headers["X-Frame-Options"] = "DENY"
    response.headers["Referrer-Policy"] = "no-referrer"
    response.headers["Cache-Control"] = "no-store"
    return response


app.include_router(public.router)
app.include_router(auth.router)
app.include_router(officer.router)


@app.get("/", tags=["Umum"])
def root() -> dict:
    return {"status": "ok", "service": settings.app_name}


@app.get("/up", tags=["Umum"])
def health() -> dict:
    return {"status": "healthy"}
