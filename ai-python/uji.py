"""Jalankan seluruh data uji lewat analyze() dan hitung akurasi.

Ada jeda 4 detik antar panggilan untuk menghindari rate limit Gemini.
"""

import time

from ai import analyze
from data_uji import DATA

JEDA_DETIK = 4


def potong(teks, panjang=60):
    """Potong teks agar ringkas untuk ditampilkan."""
    if not isinstance(teks, str):
        return ""
    teks = teks.replace("\n", " ").strip()
    return teks if len(teks) <= panjang else teks[: panjang - 3] + "..."


def main():
    total = len(DATA)
    benar = 0
    merah_benar = 0
    merah_total = 0

    for nomor, (teks, harapan) in enumerate(DATA, start=1):
        hasil = analyze(teks)
        prediksi = hasil.get("urgensi", "")

        cocok = prediksi == harapan
        benar += 1 if cocok else 0

        if harapan == "MERAH":
            merah_total += 1
            merah_benar += 1 if prediksi == "MERAH" else 0

        tanda = "BENAR" if cocok else "SALAH"
        print(
            f"{nomor:>2}. [{tanda}] harap={harapan:<6} hasil={prediksi:<6} "
            f"| {potong(hasil.get('ringkasan', ''))}"
        )

        if nomor < total:
            time.sleep(JEDA_DETIK)

    akurasi = (benar / total * 100) if total else 0.0
    print("-" * 60)
    print(f"Akurasi total : {benar}/{total} ({akurasi:.1f}%)")
    print(f"Deteksi MERAH : {merah_benar}/{merah_total}")


if __name__ == "__main__":
    main()