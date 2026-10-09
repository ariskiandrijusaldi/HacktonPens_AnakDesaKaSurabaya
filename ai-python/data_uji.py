"""Data uji fiktif untuk pengujian akurasi analyze().

Berisi 12 tuple (teks, urgensi_benar): 4 MERAH, 4 KUNING, 4 HIJAU.
Semua data fiktif dan memuat variasi bahasa gaul, typo, serta nama yang
disebut tanpa kata "Pak/Bu".
"""

DATA = [
    # ---------------------------- MERAH (4) ----------------------------
    (
        "Dita lagi di kamar kos, dia sempat kirim chat mau bunuh diri pake "
        "silet. Sekarang dihubungin ga respon, tolong cek sekrang juga!",
        "MERAH",
    ),
    (
        "brt, si Rian mukulin Andi di parkiran gedung C barusan. Andi "
        "berdarah di kepala, pelaku masih di lokasi bawa botol.",
        "MERAH",
    ),
    (
        "Tolong, saya diancam dibunuh oleh senior saya jika melapor. Dia "
        "bilang bawa senjata dan tahu alamat kos saya.",
        "MERAH",
    ),
    (
        "Kekerasan lagi terjadi di basecamp, Rara ditampar dan dicekik sampe "
        "ga bisa napas. Pelaku skrg nguamuk, ada senjata tajam.",
        "MERAH",
    ),
    # ---------------------------- KUNING (4) ---------------------------
    (
        "Saya dilecehkan secara verbal berulang kali oleh salah satu dosen. "
        "Sudah 5 kali kejadian, saya jadi susah tidur dan takut kuliah.",
        "KUNING",
    ),
    (
        "Temen saya Galih selalu dpt pesan jorok dr mantannya hampir tiap "
        "hari. Dia udah stress berat, tp blm ada kekerasan fisik.",
        "KUNING",
    ),
    (
        "Aku terus dijadiin bahan lelucon sexis sama anak angkatan depan "
        "secara berulang. Bikin aku makin cemas dan down.",
        "KUNING",
    ),
    (
        "Seseorang menguntit dan memfoto saya diam-diam beberapa minggu ini. "
        "Saya merasa tertekan dan tidak aman, tapi belum ada ancaman nyawa.",
        "KUNING",
    ),
    # ---------------------------- HIJAU (4) ----------------------------
    (
        "Mau tanya prosedur konsultasi awal soal pelecehan yang saya alami. "
        "Belum mau melapor resmi, cuma pengen tahu alurnya.",
        "HIJAU",
    ),
    (
        "Ada candaan kurang pantas di grup, cuma sekali dan ga ngarah ke "
        "orang tertentu. Pengen tau apakah ini termasuk pelanggaran.",
        "HIJAU",
    ),
    (
        "sy pgn nanya, apakah kata-kata kasar yg gk sengaja itu pelanggaran? "
        "cuma pingin konsultasi ringan aja.",
        "HIJAU",
    ),
    (
        "Saya ingin bertanya tentang aturan berpakaian yang sempat bikin "
        "teman tersinggung. Ini baru konsultasi awal saja.",
        "HIJAU",
    ),
]