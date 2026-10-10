"""Prefilter lokal (regex) untuk meredaksi data pribadi SEBELUM dikirim ke Gemini.

Ini lapisan pertahanan pertama sekaligus fallback bila AI gagal. Tidak ada
ketergantungan jaringan di sini.
"""

from __future__ import annotations

import re

# Urutan penting: URL & email lebih dulu agar `@username` tidak menelan domain email,
# lalu telepon sebelum NIM (nomor telepon juga berupa deretan digit).
_PATTERNS: list[tuple[re.Pattern[str], str]] = [
    (re.compile(r"https?://\S+|www\.\S+", re.IGNORECASE), "[URL]"),
    (re.compile(r"[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}"), "[EMAIL]"),
    (re.compile(r"(?:(?:\+62|62|0)8[1-9][0-9]{6,11})"), "[NO-HP]"),
    (re.compile(r"(?<!\d)\d{8,14}(?!\d)"), "[NIM]"),
    (re.compile(r"(?<!\w)@[A-Za-z0-9_.]{3,}"), "[AKUN]"),
]


def redact(text: str) -> str:
    """Ganti nomor HP, email, NIM, URL, dan username @ dengan placeholder."""
    if not text:
        return ""
    result = text
    for pattern, placeholder in _PATTERNS:
        result = pattern.sub(placeholder, result)
    return result


def contains_prompt_injection(text: str) -> bool:
    """Deteksi sederhana upaya prompt-injection untuk penanda tambahan."""
    lowered = text.lower()
    markers = (
        "abaikan instruksi",
        "ignore previous",
        "ignore all",
        "klasifikasikan sebagai",
        "system prompt",
        "you are now",
        "kamu sekarang",
    )
    return any(m in lowered for m in markers)
