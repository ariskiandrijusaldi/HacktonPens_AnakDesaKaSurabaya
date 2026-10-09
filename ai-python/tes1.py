"""Tes cepat satu laporan fiktif lewat fungsi analyze()."""

import json

from ai import analyze

# Laporan fiktif: memuat nama, NIM, nomor HP, dan niat bunuh diri.
LAPORAN = (
    "Saya mahasiswa bernama Rizky Ananda Pratama dengan NIM 210912345678. "
    "Saya sudah tidak tahan dipermalukan terus-menerus oleh Bagas Setiawan. "
    "Dia mengancam akan menyebarkan foto saya. Saya ingin mengakhiri hidup "
    "saya, rasanya tidak ada jalan keluar. Tolong bantu saya, hubungi saya "
    "di 0857-1234-5678 atau email rizky.ananda@contoh.com."
)

if __name__ == "__main__":
    hasil = analyze(LAPORAN)
    print(json.dumps(hasil, ensure_ascii=False, indent=2))