"""Test endpoint petugas: otorisasi, privasi data, dan override identitas."""

from fastapi.testclient import TestClient


def _create_and_find(client: TestClient, create_report, headers: dict, **kwargs) -> tuple[str, str]:
    """Buat laporan lalu cari id-nya via daftar petugas (berdasarkan ticket_code)."""
    code = create_report(**kwargs).json()["ticket_code"]
    resp = client.get("/api/officer/reports", headers=headers, params={"limit": 100})
    assert resp.status_code == 200, resp.text
    item = next((i for i in resp.json()["items"] if i["ticket_code"] == code), None)
    assert item is not None, "laporan yang baru dibuat tidak ditemukan di daftar"
    return item["id"], code


def test_officer_endpoints_require_auth(client: TestClient):
    assert client.get("/api/officer/reports").status_code == 401
    assert client.get("/api/officer/stats").status_code == 401


def test_officer_can_list_and_filter(client: TestClient, member_headers, create_report):
    create_report()
    resp = client.get("/api/officer/reports", headers=member_headers)
    assert resp.status_code == 200
    body = resp.json()
    assert {"items", "total", "page", "limit"} <= set(body.keys())
    assert body["total"] >= 1


def test_report_detail_hides_secrets(client: TestClient, admin_headers, create_report):
    report_id, _ = _create_and_find(client, create_report, admin_headers, contact="0812-9999-8888")

    resp = client.get(f"/api/officer/reports/{report_id}", headers=admin_headers)
    assert resp.status_code == 200
    body = resp.json()
    assert "original_text" not in body
    assert "contact" not in body
    # Kontak boleh dibuka hanya lewat override.
    assert body["contact_available"] is True
    assert "sanitized_text" in body


def test_override_requires_admin(client: TestClient, member_headers, create_report):
    report_id, _ = _create_and_find(client, create_report, member_headers, contact="0812-9999-8888")

    resp = client.post(
        f"/api/officer/reports/{report_id}/override",
        json={"pin": "123456", "confirm_pin": "123456", "reason": "x" * 25},
        headers=member_headers,
    )
    assert resp.status_code == 403


def test_override_admin_success(client: TestClient, admin_headers, create_report):
    report_id, _ = _create_and_find(client, create_report, admin_headers, contact="0812-9999-8888")

    resp = client.post(
        f"/api/officer/reports/{report_id}/override",
        json={"pin": "123456", "confirm_pin": "123456", "reason": "Investigasi darurat sesuai SOP."},
        headers=admin_headers,
    )
    assert resp.status_code == 200, resp.text
    body = resp.json()
    assert body["contact"] == "0812-9999-8888"
    assert body["original_text"]


def test_override_reason_too_short(client: TestClient, admin_headers, create_report):
    report_id, _ = _create_and_find(client, create_report, admin_headers, contact="0812-9999-8888")

    resp = client.post(
        f"/api/officer/reports/{report_id}/override",
        json={"pin": "123456", "confirm_pin": "123456", "reason": "singkat"},
        headers=admin_headers,
    )
    assert resp.status_code == 422


def test_override_wrong_pin(client: TestClient, admin_headers, create_report):
    report_id, _ = _create_and_find(client, create_report, admin_headers)

    resp = client.post(
        f"/api/officer/reports/{report_id}/override",
        json={"pin": "000000", "confirm_pin": "000000", "reason": "Percobaan PIN salah."},
        headers=admin_headers,
    )
    assert resp.status_code == 403


def test_patterns_detects_repeated_perpetrator(client: TestClient, admin_headers, create_report):
    create_report()
    create_report()

    resp = client.get("/api/officer/patterns", headers=admin_headers)
    assert resp.status_code == 200
    patterns = resp.json()
    assert any(p["report_count"] >= 2 for p in patterns)


def test_location_shown_and_filterable(client: TestClient, admin_headers, create_report):
    report_id, _ = _create_and_find(
        client, create_report, admin_headers, location="Gedung B Fakultas Teknik"
    )

    detail = client.get(f"/api/officer/reports/{report_id}", headers=admin_headers)
    assert detail.status_code == 200
    assert detail.json()["location"] == "Gedung B Fakultas Teknik"

    filtered = client.get(
        "/api/officer/reports", headers=admin_headers, params={"location": "Fakultas Teknik"}
    )
    assert filtered.status_code == 200
    assert any(i["id"] == report_id for i in filtered.json()["items"])


def test_location_pii_is_redacted(client: TestClient, admin_headers, create_report):
    report_id, _ = _create_and_find(
        client, create_report, admin_headers, location="Hubungi saya di 081234567890"
    )
    detail = client.get(f"/api/officer/reports/{report_id}", headers=admin_headers).json()
    assert "[NO-HP]" in detail["location"]
    assert "081234567890" not in detail["location"]


def test_audit_logs_admin_only(client: TestClient, admin_headers, member_headers):
    assert client.get("/api/officer/audit-logs", headers=member_headers).status_code == 403
    resp = client.get("/api/officer/audit-logs", headers=admin_headers)
    assert resp.status_code == 200
    assert isinstance(resp.json(), list)
