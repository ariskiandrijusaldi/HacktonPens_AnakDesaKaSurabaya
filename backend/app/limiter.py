"""Rate limiter global (slowapi) berbasis memori.

Catatan privasi: kunci rate limit memakai IP request yang hidup hanya di memori
proses. Tidak ada IP yang ditulis ke database.
"""

from __future__ import annotations

from slowapi import Limiter
from slowapi.util import get_remote_address

limiter = Limiter(key_func=get_remote_address)
