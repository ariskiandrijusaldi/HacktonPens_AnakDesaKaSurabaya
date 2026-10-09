"""Uji kebocoran data pribadi pada hasil analyze().

Kebijakan sensor:
- Nama korban dan saksi harus HILANG.
- NIM, nomor HP, dan email harus HILANG.
- Nama pelaku harus TETAP TAMPIL.
"""

from ai import analyze

# Teks fiktif dengan nama korban, saksi, pelaku, NIM, HP (+62/dash), email.
TEKS = (
    "Saya Sinta Maharani dengan NIM 210987654321. Saksi bernama Dewi Lestari, "
    "sedangkan pelaku bernama Budi Hartono. Hubungi saya di 0857-1234-5678 "
    "atau nomor cadangan +62 812 9988 7766. Email saya sinta.maharani@contoh.com."
)

# Harus HILANG dari teks_bersih.
KATA_HARUS_HILANG = [
    "Sinta Maharani",
    "Dewi Lestari",
    "210987654321",
    "0857-1234-5678",
    "+62 812 9988 7766",
    "sinta.maharani@contoh.com",
]

# Harus TETAP TAMPIL di teks_bersih (nama pelaku).
KATA_HARUS_TAMPIL = [
    "Budi Hartono",
]

if __name__ == "__main__":
    hasil = analyze(TEKS)
    teks_bersih = hasil.get("teks_bersih", "")
    print("teks_bersih:", teks_bersih)
    print("--- harus hilang (harap 'aman') ---")
    for kata in KATA_HARUS_HILANG:
        status = "BOCOR" if kata in teks_bersih else "aman"
        print(f"{status}\t{kata}")
    print("--- harus tampil (harap 'tampil') ---")
    for kata in KATA_HARUS_TAMPIL:
        status = "tampil" if kata in teks_bersih else "hilang"
        print(f"{status}\t{kata}")