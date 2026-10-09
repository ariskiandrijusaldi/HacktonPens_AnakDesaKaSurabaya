"""Seed laporan demo untuk presentasi.

Jalankan: `python seed_demo.py`
Memasukkan ~8 laporan fiktif (campuran MERAH/KUNING/HIJAU, 1 terlapor berulang).
"""

from __future__ import annotations

from app.database import Base, SessionLocal, engine
from app.seed import seed_demo_reports, seed_officers


def main() -> None:
    Base.metadata.create_all(bind=engine)
    db = SessionLocal()
    try:
        seed_officers(db)
        count = seed_demo_reports(db)
        print(f"{count} laporan demo berhasil dibuat.")
    finally:
        db.close()


if __name__ == "__main__":
    main()
