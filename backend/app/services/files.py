"""Validasi & sanitasi lampiran.

Keamanan:
- Cek MAGIC BYTES, bukan hanya ekstensi/MIME dari klien.
- Gambar dirender ulang dengan Pillow untuk membuang EXIF/GPS, dengan batas
  dimensi agar tidak kena decompression bomb.
- PDF dibersihkan metadata (Info + XMP + embedded files/JavaScript).
- Nama file diacak, disimpan di luar direktori publik.
- SHA-256 hanya untuk integritas, bukan enkripsi.

Validasi semua file selesai SEBELUM satu byte pun ditulis ke disk, sehingga
kegagalan validasi tidak meninggalkan file orphan.
"""

from __future__ import annotations

import io
import logging
import secrets

from fastapi import HTTPException, UploadFile, status
from PIL import Image

from app.config import get_settings
from app.models import Attachment
from app.security import sha256_hex

logger = logging.getLogger(__name__)
settings = get_settings()

# Batas dimensi gambar (piksel total) untuk mencegah decompression bomb.
MAX_IMAGE_PIXELS = 40_000_000
Image.MAX_IMAGE_PIXELS = MAX_IMAGE_PIXELS

_ALLOWED = {
    "image/jpeg": ".jpg",
    "image/png": ".png",
    "application/pdf": ".pdf",
}


def detect_mime(data: bytes) -> str | None:
    """Deteksi MIME dari magic bytes. Kembalikan None jika tidak didukung."""
    if data.startswith(b"\xff\xd8\xff"):
        return "image/jpeg"
    if data.startswith(b"\x89PNG\r\n\x1a\n"):
        return "image/png"
    if data.startswith(b"%PDF-"):
        return "application/pdf"
    return None


def _sanitize_image(data: bytes, mime: str) -> bytes:
    """Render ulang gambar untuk menghapus EXIF/GPS. Raise ValueError bila korup/bomb."""
    with Image.open(io.BytesIO(data)) as img:
        width, height = img.size
        if width * height > MAX_IMAGE_PIXELS:
            raise ValueError("Dimensi gambar terlalu besar.")
        img.load()
        mode = "RGB" if mime == "image/jpeg" else img.mode
        if mode not in {"RGB", "RGBA", "L"}:
            mode = "RGB"
        # `paste` dari hasil `convert` membuang metadata tanpa memuat list piksel.
        converted = img.convert(mode)
        clean = Image.new(mode, converted.size)
        clean.paste(converted)
        out = io.BytesIO()
        if mime == "image/jpeg":
            clean.save(out, format="JPEG", quality=90)
        else:
            clean.save(out, format="PNG")
        return out.getvalue()


def _sanitize_pdf(data: bytes) -> bytes:
    """Bersihkan metadata PDF: Info, XMP, embedded files, dan JavaScript."""
    from pypdf import PdfReader, PdfWriter

    reader = PdfReader(io.BytesIO(data))
    if reader.is_encrypted:
        raise ValueError("PDF terenkripsi tidak didukung.")

    writer = PdfWriter()
    for page in reader.pages:
        writer.add_page(page)

    # Hapus metadata dokumen (Info + XMP).
    writer.metadata = None
    writer.xmp_metadata = None

    # Hapus aksi otomatis, JavaScript, dan embedded files.
    root = writer.root_object
    for key in ("/OpenAction", "/AA", "/JavaScript"):
        if key in root:
            del root[key]
    names = root.get("/Names")
    if names is not None:
        names_obj = names.get_object() if hasattr(names, "get_object") else names
        if isinstance(names_obj, dict):
            names_obj.pop("/EmbeddedFiles", None)
            names_obj.pop("/JavaScript", None)
            if not names_obj:
                del root["/Names"]

    out = io.BytesIO()
    writer.write(out)
    return out.getvalue()


def _validate_one(upload: UploadFile) -> tuple[str, bytes]:
    """Validasi & sanitasi satu file. Kembalikan (mime, data_bersih). Tidak menulis disk."""
    # Baca maksimal max+1 byte agar file raksasa tidak memuat seluruh memori.
    data = upload.file.read(settings.max_file_size_bytes + 1)
    if len(data) > settings.max_file_size_bytes:
        # Literal 413 agar kompatibel lintas versi Starlette (nama konstanta berubah).
        raise HTTPException(
            status_code=413,
            detail=f"Ukuran file melebihi {settings.max_file_size_mb} MB.",
        )
    if not data:
        raise HTTPException(status_code=status.HTTP_400_BAD_REQUEST, detail="File kosong tidak diizinkan.")

    mime = detect_mime(data)
    if mime is None:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Format file tidak didukung. Hanya JPG, PNG, dan PDF.",
        )

    if mime in {"image/jpeg", "image/png"}:
        try:
            data = _sanitize_image(data, mime)
        except Exception as exc:  # gambar korup / terlalu besar
            logger.warning("Gagal memproses gambar: %s", exc)
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="Gambar tidak valid atau rusak.",
            ) from exc
    elif mime == "application/pdf":
        try:
            data = _sanitize_pdf(data)
        except HTTPException:
            raise
        except Exception as exc:  # PDF korup / terenkripsi
            logger.warning("Gagal memproses PDF: %s", exc)
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="PDF tidak valid atau rusak.",
            ) from exc

    return mime, data


def process_uploads(uploads: list[UploadFile], report_id: str) -> list[Attachment]:
    """Validasi, sanitasi, simpan lampiran, dan kembalikan baris Attachment."""
    if not uploads:
        return []
    if len(uploads) > settings.max_files_per_report:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=f"Maksimal {settings.max_files_per_report} lampiran per laporan.",
        )

    # Fase 1: validasi & sanitasi SEMUA file (tanpa menyentuh disk).
    prepared = [_validate_one(upload) for upload in uploads]

    # Fase 2: tulis ke disk. Bersihkan bila ada kegagalan di tengah jalan.
    upload_dir = settings.upload_dir_path
    upload_dir.mkdir(parents=True, exist_ok=True)

    attachments: list[Attachment] = []
    written_paths: list = []
    try:
        for mime, data in prepared:
            filename_random = secrets.token_hex(16) + _ALLOWED[mime]
            path = upload_dir / filename_random
            path.write_bytes(data)
            written_paths.append(path)
            attachments.append(
                Attachment(
                    report_id=report_id,
                    filename_random=filename_random,
                    mime=mime,
                    size=len(data),
                    sha256=sha256_hex(data),
                    path=str(path.as_posix()),
                )
            )
    except Exception:
        for path in written_paths:
            path.unlink(missing_ok=True)
        raise
    return attachments


def remove_attachment_files(attachments: list[Attachment]) -> None:
    """Hapus file lampiran dari disk. Dipakai saat commit DB gagal (cegah orphan)."""
    from pathlib import Path

    for attachment in attachments:
        if not attachment.path:
            continue
        Path(attachment.path).unlink(missing_ok=True)