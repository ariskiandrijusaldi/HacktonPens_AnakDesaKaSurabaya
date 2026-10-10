"""Test validasi & sanitasi lampiran: magic bytes, EXIF, metadata PDF, ukuran."""

import io

from fastapi.testclient import TestClient
from PIL import Image
from pypdf import PdfWriter

_TEXT = "Saya mengalami kekerasan dan memerlukan pendampingan segera di kampus."


def _create_with_file(client: TestClient, mock_gemini, filename: str, data: bytes, mime: str):
    return client.post(
        "/api/reports",
        data={"text": _TEXT, "victim_wish": "PENDAMPINGAN", "consent": "true"},
        files=[("files", (filename, data, mime))],
    )


def _download_attachment(client: TestClient, headers: dict, ticket_code: str) -> bytes:
    items = client.get("/api/officer/reports", headers=headers, params={"limit": 100}).json()["items"]
    report_id = next(i["id"] for i in items if i["ticket_code"] == ticket_code)
    detail = client.get(f"/api/officer/reports/{report_id}", headers=headers).json()
    attachment_id = detail["attachments"][0]["id"]
    resp = client.get(
        f"/api/officer/reports/{report_id}/attachments/{attachment_id}", headers=headers
    )
    assert resp.status_code == 200, resp.text
    return resp.content


def test_unsupported_file_rejected(client: TestClient, mock_gemini):
    resp = _create_with_file(client, mock_gemini, "malware.exe", b"MZ\x90\x00binary", "application/x-msdownload")
    assert resp.status_code == 400


def test_oversized_file_rejected(client: TestClient, mock_gemini):
    big = b"\xff\xd8\xff" + b"0" * (5 * 1024 * 1024)
    resp = _create_with_file(client, mock_gemini, "big.jpg", big, "image/jpeg")
    assert resp.status_code == 413


def test_jpeg_exif_is_stripped(client: TestClient, mock_gemini, admin_headers):
    img = Image.new("RGB", (30, 30), (180, 20, 20))
    exif = img.getexif()
    exif[0x010E] = "SECRET-EXIF-LOCATION"  # ImageDescription
    buf = io.BytesIO()
    img.save(buf, format="JPEG", exif=exif.tobytes())

    resp = _create_with_file(client, mock_gemini, "foto.jpg", buf.getvalue(), "image/jpeg")
    assert resp.status_code == 201, resp.text
    stored = _download_attachment(client, admin_headers, resp.json()["ticket_code"])
    assert b"SECRET-EXIF-LOCATION" not in stored


def test_pdf_metadata_is_stripped(client: TestClient, mock_gemini, admin_headers):
    writer = PdfWriter()
    writer.add_blank_page(width=100, height=100)
    writer.add_metadata({"/Author": "SECRET-AUTHOR-NAME", "/Producer": "SecretTool"})
    buf = io.BytesIO()
    writer.write(buf)

    resp = _create_with_file(client, mock_gemini, "bukti.pdf", buf.getvalue(), "application/pdf")
    assert resp.status_code == 201, resp.text
    stored = _download_attachment(client, admin_headers, resp.json()["ticket_code"])
    assert b"SECRET-AUTHOR-NAME" not in stored
    assert b"SecretTool" not in stored