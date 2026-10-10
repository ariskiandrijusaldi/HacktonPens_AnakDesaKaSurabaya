"""Test jalur fallback triase AI: laporan tidak boleh hilang saat Gemini gagal."""

from fastapi.testclient import TestClient

from app.config import get_settings
from app.schemas import Urgency
from app.services import ai


def test_triage_without_api_key_uses_safe_fallback():
    # conftest menyetel GEMINI_API_KEY="" sehingga triase memakai fallback lokal.
    result, ai_failed = ai.triage("Saya merasa terancam oleh seseorang di kampus.")
    assert ai_failed is True
    assert result.urgency == Urgency.KUNING


def test_triage_retries_then_falls_back(monkeypatch):
    monkeypatch.setattr(get_settings(), "gemini_api_key", "test-key")
    calls = {"n": 0}

    def _boom(text: str):
        calls["n"] += 1
        raise RuntimeError("jaringan mati")

    monkeypatch.setattr(ai, "_call_gemini", _boom)

    result, ai_failed = ai.triage("Teks laporan yang cukup panjang untuk dianalisis.")
    assert ai_failed is True
    assert result.urgency == Urgency.KUNING
    assert calls["n"] == 2  # 1 percobaan + 1 retry


def test_report_still_saved_when_ai_fails(client: TestClient, monkeypatch):
    monkeypatch.setattr(get_settings(), "gemini_api_key", "test-key")

    def _boom(text: str):
        raise RuntimeError("jaringan mati")

    monkeypatch.setattr(ai, "_call_gemini", _boom)

    resp = client.post(
        "/api/reports",
        data={
            "text": "Saya mengalami kekerasan dan butuh pertolongan segera di kampus.",
            "victim_wish": "PENDAMPINGAN",
            "consent": "true",
        },
    )
    assert resp.status_code == 201, resp.text
    assert resp.json()["ticket_code"].startswith("PPKS-")