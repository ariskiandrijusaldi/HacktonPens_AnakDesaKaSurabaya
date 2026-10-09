"""Modul AI Agen Satgas PPKS Kampus.

Berisi fungsi analyze() untuk menyensor data pribadi dan menilai tingkat
urgensi sebuah laporan PPKS menggunakan Gemini.
"""

import json
import os
import re
import time
from pathlib import Path

from dotenv import load_dotenv
from google import genai
from google.genai import types

# Muat variabel dari file .env yang berada di folder yang sama dengan file ini.
load_dotenv(Path(__file__).resolve().parent / ".env")

# ---------------------------------------------------------------------------
# Lapisan kedua penyensoran data pribadi (regex)
# ---------------------------------------------------------------------------

# Alamat email.
_RE_EMAIL = re.compile(r"[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}")

# Nomor HP Indonesia: diawali +62, 62, atau 0 lalu angka 8, boleh ada tanda
# hubung / spasi sebagai pemisah. Contoh: 0857-1234-5678, +62 812 3456 7890.
_RE_HP = re.compile(r"(?:(?:\+62)|(?:62)|(?:0))8[\d\s\-]{7,14}")

# Angka identitas (NIM) sepanjang 8-12 digit.
_RE_NIM = re.compile(r"\b\d{8,12}\b")


def sapu(teks: str) -> str:
    """Sensor data pribadi dengan regex sebagai lapisan kedua.

    Mengganti email menjadi [EMAIL], nomor HP menjadi [HP], dan angka
    8-12 digit menjadi [NIM].
    """
    if not isinstance(teks, str):
        return teks
    hasil = _RE_EMAIL.sub("[EMAIL]", teks)
    hasil = _RE_HP.sub("[HP]", hasil)
    hasil = _RE_NIM.sub("[NIM]", hasil)
    return hasil


# ---------------------------------------------------------------------------
# Prompt untuk Gemini
# ---------------------------------------------------------------------------

_PROMPT = """Kamu adalah asisten analisis laporan Satgas PPKS (Pencegahan dan \
Penanganan Kekerasan Seksual) di lingkungan kampus. Analisis laporan berikut.

Aturan penyensoran (WAJIB):
1. Sensor data berikut (nama bisa disebut dengan maupun tanpa kata \
"Pak/Bu/Mas/Mbak"):
   - Nama orang korban/pengadu -> WAJIB diganti dengan kata "Korban" \
(ditulis [Korban])
   - Nama orang saksi -> [Saksi]
   - NIM -> [NIM]
   - Nomor HP -> [HP]
   - Email -> [EMAIL]
   - Alamat -> [Alamat]
2. Setiap kali nama korban/pengadu disebut, GANTI dengan [Korban]. Jangan \
sekadar menghapus nama itu dan jangan membiarkannya; kata [Korban] harus \
tetap muncul di posisi yang sama.
3. Nama orang TERLAPOR/PELAKU tetap DIBIARKAN tampil apa adanya, JANGAN \
disensor atau diganti dengan token apa pun.
   Pastikan tidak ada nama korban, nama saksi, atau angka identitas yang \
tersisa di teks_bersih, tetapi nama pelaku tetap muncul.

Aturan urgensi:
- MERAH = ancaman terhadap nyawa, kekerasan sedang berlangsung, niat bunuh \
diri, pemukulan, atau penggunaan senjata.
- KUNING = tekanan psikologis berat atau pelecehan yang berulang.
- HIJAU = pelanggaran ringan atau konsultasi awal.
- Jika ragu antara dua level, pilih level yang LEBIH TINGGI.

Aturan ringkasan:
- Buat ringkasan 2 kalimat yang menjelaskan inti KEJADIAN SPESIFIK pada \
laporan ini (siapa melakukan apa, konteks, dan dampaknya), bukan kalimat umum.
- Sebut nama pelaku apa adanya (boleh tampil). Data pribadi korban/saksi \
(nama korban/saksi, NIM, HP, email, alamat) TIDAK boleh dimuat.

Aturan alasan_urgensi:
- Jelaskan secara analitis MENGAPA level urgensi tersebut dipilih, dengan \
MENGUTIP atau MERUJUK detail nyata yang ada di laporan, bukan pernyataan umum.
- Kaitkan indikator spesifik tersebut dengan kriteria MERAH/KUNING/HIJAU.
- Nama pelaku boleh disebut; data pribadi korban/saksi dilarang. Tulis 1-3 \
kalimat.

Aturan rekomendasi:
- Berikan 2-4 rekomendasi tindakan konkret untuk Satgas PPKS sebagai daftar \
array string, urut dari yang paling mendesak.
- Rekomendasi WAJIB disesuaikan dengan detail spesifik laporan ini, misalnya \
jenis kekerasan, pola kejadian, tingkat risiko, dan kebutuhan korban.
- DILARANG memakai kalimat template yang sama untuk laporan berbeda. Susun \
kalimat sendiri, jangan menyalin frasa contoh.
- Sebut nama pelaku bila relevan; data pribadi korban/saksi dilarang.

Aturan variasi:
- Untuk setiap laporan, gunakan pilihan kata dan struktur kalimat yang berbeda.
- Hindari membuka ringkasan/alasan dengan frasa klise yang berulang.

Kembalikan HANYA JSON valid dengan format:
{"teks_bersih": "...", "urgensi": "MERAH|KUNING|HIJAU", "ringkasan": "...", \
"alasan_urgensi": "...", "rekomendasi": ["...", "..."]}

Laporan:
"""

# Konfigurasi keamanan: izinkan konten sensitif karena laporan PPKS wajar
# memuat materi tersebut, penilaian tetap dilakukan oleh model.
_SAFETY_SETTINGS = [
    types.SafetySetting(
        category=types.HarmCategory.HARM_CATEGORY_SEXUALLY_EXPLICIT,
        threshold=types.HarmBlockThreshold.BLOCK_NONE,
    ),
    types.SafetySetting(
        category=types.HarmCategory.HARM_CATEGORY_HARASSMENT,
        threshold=types.HarmBlockThreshold.BLOCK_NONE,
    ),
    types.SafetySetting(
        category=types.HarmCategory.HARM_CATEGORY_DANGEROUS_CONTENT,
        threshold=types.HarmBlockThreshold.BLOCK_NONE,
    ),
    types.SafetySetting(
        category=types.HarmCategory.HARM_CATEGORY_HATE_SPEECH,
        threshold=types.HarmBlockThreshold.BLOCK_NONE,
    ),
]

_URGENSI_VALID = {"MERAH", "KUNING", "HIJAU"}

# Model cadangan bila model utama sedang penuh (503/429). Dicoba berurutan.
_MODEL_CADANGAN = [
    "gemini-flash-lite-latest",
    "gemini-3.5-flash-lite",
    "gemini-3.1-flash-lite",
    "gemini-2.5-flash-lite",
    "gemini-flash-latest",
]

# Jumlah percobaan ulang per model untuk error sementara, dan jeda dasarnya.
_MAX_PERCOBAAN = 3
_JEDA_DETIK = 3

# Penanda error sementara yang layak dicoba ulang / pindah model.
_ERROR_SEMENTARA = (
    "503",
    "429",
    "500",
    "UNAVAILABLE",
    "RESOURCE_EXHAUSTED",
    "overloaded",
    "high demand",
)

_GAGAL = {
    "teks_bersih": "[gagal disensor, tinjau manual]",
    "urgensi": "MERAH",
    "ringkasan": "Gagal dianalisis otomatis, tinjau segera",
    "alasan_urgensi": "Analisis otomatis gagal sehingga laporan dianggap berisiko tinggi.",
    "rekomendasi": [
        "Tinjau laporan secara manual oleh petugas Satgas PPKS.",
        "Hubungi pelapor secepatnya untuk verifikasi dan penanganan.",
    ],
}


def _daftar_model(utama: str) -> list:
    """Susun urutan model: model utama dulu, lalu cadangan (tanpa duplikat)."""
    urutan = [utama] + _MODEL_CADANGAN
    hasil = []
    for m in urutan:
        if m and m not in hasil:
            hasil.append(m)
    return hasil


def _panggil_gemini(client, model_utama: str, teks: str):
    """Panggil Gemini dengan retry dan fallback antar model.

    Error sementara (503/429/500) dicoba ulang beberapa kali, lalu pindah ke
    model cadangan. Error non-sementara langsung dilempar ke pemanggil.
    """
    error_terakhir = None
    for model in _daftar_model(model_utama):
        for percobaan in range(1, _MAX_PERCOBAAN + 1):
            try:
                return client.models.generate_content(
                    model=model,
                    contents=_PROMPT + teks,
                    config=types.GenerateContentConfig(
                        response_mime_type="application/json",
                        temperature=0.4,
                        safety_settings=_SAFETY_SETTINGS,
                    ),
                )
            except Exception as e:  # noqa: BLE001 - dipilah berdasarkan pesan
                error_terakhir = e
                pesan = str(e)
                sementara = any(k in pesan for k in _ERROR_SEMENTARA)
                print(
                    f"[PERINGATAN] model {model} gagal "
                    f"(percobaan {percobaan}/{_MAX_PERCOBAAN}): {pesan[:120]}"
                )
                if not sementara:
                    raise
                if percobaan < _MAX_PERCOBAAN:
                    time.sleep(_JEDA_DETIK * percobaan)
    raise error_terakhir


def analyze(teks: str) -> dict:
    """Analisis satu laporan PPKS dengan Gemini.

    Mengembalikan dict dengan kunci: teks_bersih, urgensi, ringkasan,
    alasan_urgensi, dan rekomendasi (list langkah tindakan).
    Jika terjadi error apa pun, mengembalikan nilai fallback yang aman.
    """
    try:
        api_key = os.getenv("GEMINI_API_KEY")
        model = os.getenv("GEMINI_MODEL", "gemini-flash-lite-latest")

        client = genai.Client(api_key=api_key)
        response = _panggil_gemini(client, model, teks)

        data = json.loads(response.text)

        teks_bersih = data.get("teks_bersih", "")
        urgensi = str(data.get("urgensi", "")).strip().upper()
        ringkasan = data.get("ringkasan", "")
        alasan_urgensi = data.get("alasan_urgensi", "")

        # Rekomendasi harus berupa list; jika model mengirim string, bungkus.
        rekomendasi = data.get("rekomendasi", [])
        if isinstance(rekomendasi, str):
            rekomendasi = [rekomendasi] if rekomendasi.strip() else []
        elif not isinstance(rekomendasi, list):
            rekomendasi = []

        # Lapisan kedua: sensor ulang dengan regex.
        teks_bersih = sapu(teks_bersih)

        # Validasi urgensi.
        if urgensi not in _URGENSI_VALID:
            urgensi = "MERAH"

        return {
            "teks_bersih": teks_bersih,
            "urgensi": urgensi,
            "ringkasan": ringkasan,
            "alasan_urgensi": alasan_urgensi,
            "rekomendasi": rekomendasi,
        }
    except Exception as e:  # noqa: BLE001 - semua error dikembalikan ke fallback
        print(f"[ERROR analyze] {type(e).__name__}: {e}")
        return dict(_GAGAL)