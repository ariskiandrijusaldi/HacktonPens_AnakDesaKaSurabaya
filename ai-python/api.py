"""API FastAPI untuk analisis laporan PPKS.

Jalankan dengan: uvicorn api:app --port 8001
"""

from fastapi import FastAPI
from pydantic import BaseModel

from ai import analyze

app = FastAPI(title="API Agen Satgas PPKS")


class PermintaanAnalisis(BaseModel):
    """Body request berisi teks laporan."""

    teks: str


@app.post("/analyze")
def analyze_endpoint(permintaan: PermintaanAnalisis) -> dict:
    """Terima laporan dan kembalikan hasil analyze()."""
    return analyze(permintaan.teks)