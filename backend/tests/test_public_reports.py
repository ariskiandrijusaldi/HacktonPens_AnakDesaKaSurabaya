"""Test alur pelapor: pembuatan tiket, privasi tiket, validasi."""

import re

from fastapi.testclient import TestClient


def test_create_report_returns_ticket_only(client: TestClient, create_report):
    resp = create_report()
    assert resp.status_code == 201, resp.text
    body = resp.json()
    # Hanya ticket_code yang dikembalikan, bukan data laporan.
    assert set(body.keys()) == {"ticket_code"}
    assert body["ticket_code"].startswith("PPKS-")
    # Ticket numeric-only, otomatis dari backend (pelapor tidak mengetik).
    assert re.fullmatch(r"PPKS-\d{6}-\d{6}", body["ticket_code"]), body["ticket_code"]


def test_ticket_endpoint_does_not_leak_content(client: TestClient, create_report):
    created = create_report(text="Saya diancam seseorang bernama Budi di kampus tadi malam.")
    code = created.json()["ticket_code"]

    resp = client.get(f"/api/tickets/{code}")
    assert resp.status_code == 200
    body = resp.json()
    # Hanya status, updated_at, dan pesan SATGAS.
    assert set(body.keys()) == {"ticket_code", "status", "updated_at", "messages"}
    serialized = str(body)
    assert "diancam" not in serialized
    assert "sanitized_text" not in body
    assert "original_text" not in body


def test_invalid_ticket_returns_uniform_404(client: TestClient):
    resp = client.get("/api/tickets/PPKS-ZZZZ-ZZZZ-ZZZZ")
    assert resp.status_code == 404
    assert resp.json()["detail"] == "Tiket tidak ditemukan."


def test_report_requires_consent(client: TestClient, mock_gemini):
    resp = client.post(
        "/api/reports",
        data={
            "text": "Teks laporan yang cukup panjang untuk lolos validasi minimal.",
            "victim_wish": "KONSULTASI",
            "consent": "false",
        },
    )
    assert resp.status_code == 400


def test_report_rejects_short_text(client: TestClient, mock_gemini):
    resp = client.post(
        "/api/reports",
        data={"text": "pendek", "victim_wish": "KONSULTASI", "consent": "true"},
    )
    assert resp.status_code == 422


def test_public_message_roundtrip(client: TestClient, create_report):
    code = create_report().json()["ticket_code"]
    resp = client.post(f"/api/tickets/{code}/messages", json={"body": "Terima kasih atas bantuannya."})
    assert resp.status_code == 201


def test_help_resources(client: TestClient):
    resp = client.get("/api/help-resources")
    assert resp.status_code == 200
    assert isinstance(resp.json(), list)
    assert len(resp.json()) >= 1


def test_procedures(client: TestClient):
    resp = client.get("/api/procedures")
    assert resp.status_code == 200
    body = resp.json()
    assert body["title"]
    assert len(body["steps"]) >= 1
    assert body["steps"][0]["order"] == 1
    assert len(body["faq"]) >= 1


def test_testimonials(client: TestClient):
    resp = client.get("/api/testimonials")
    assert resp.status_code == 200
    body = resp.json()
    assert body["total"] >= 1
    assert 1 <= len(body["items"]) <= 10
    assert all("author_alias" in item for item in body["items"])

    page2 = client.get("/api/testimonials", params={"page": 2, "limit": 5})
    assert page2.status_code == 200
    assert page2.json()["page"] == 2


def test_profile(client: TestClient):
    resp = client.get("/api/profile")
    assert resp.status_code == 200
    body = resp.json()
    assert body["title"]
    assert body["tagline"]
    assert len(body["sections"]) >= 1
    assert len(body["members"]) >= 1
    assert len(body["contacts"]) >= 1
