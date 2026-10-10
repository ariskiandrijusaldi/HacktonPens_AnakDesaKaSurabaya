"""Notifikasi Telegram untuk laporan MERAH.

Kegagalan notifikasi TIDAK boleh menggagalkan penyimpanan laporan. Tidak ada
data pribadi yang dikirim; hanya ticket_code + ringkasan tersensor.
"""

from __future__ import annotations

import logging

import httpx

from app.config import get_settings

logger = logging.getLogger(__name__)
settings = get_settings()

_TELEGRAM_API = "https://api.telegram.org"


async def send_red_alert(ticket_code: str, summary: str, category: str) -> None:
    """Kirim alert ke Telegram. Aman dipanggil sebagai background task."""
    if not settings.telegram_bot_token or not settings.telegram_chat_id:
        logger.info("notifier disabled")
        return

    safe_summary = (summary or "").strip()[:300]
    text = (
        "🚨 *LAPORAN PRIORITAS MERAH*\n"
        f"Tiket: `{ticket_code}`\n"
        f"Kategori: {category}\n"
        f"Ringkasan: {safe_summary}\n"
        f"Dasbor: {settings.dashboard_url}"
    )
    url = f"{_TELEGRAM_API}/bot{settings.telegram_bot_token}/sendMessage"
    payload = {
        "chat_id": settings.telegram_chat_id,
        "text": text,
        "parse_mode": "Markdown",
        "disable_web_page_preview": True,
    }
    try:
        async with httpx.AsyncClient(timeout=10) as client:
            resp = await client.post(url, json=payload)
            if resp.status_code >= 400:
                logger.warning("Notifikasi Telegram gagal (%s): %s", resp.status_code, resp.text[:200])
    except Exception as exc:  # jaringan / timeout
        logger.warning("Notifikasi Telegram error: %s", exc)
