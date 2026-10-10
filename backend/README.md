# Agen Satgas PPKS Kampus — Backend

Backend sistem pelaporan kekerasan seksual anonim dengan triase AI untuk Satgas PPKS kampus.
Dibangun dengan **FastAPI + SQLAlchemy 2.x + SQLite**, siap dipindah ke Postgres/Supabase
lewat `DATABASE_URL`.

> Fokus utama: **keamanan data korban**. Teks asli dan kontak pelapor dienkripsi (Fernet),
> identitas terlapor disimpan sebagai HMAC, dan endpoint publik tidak menyimpan IP/User-Agent.

---

## 1. Arsitektur singkat

- **Pelapor (anonim)** mengirim laporan → prefilter regex lokal → triase Gemini (redaksi + urgensi)
  → teks asli & kontak dienkripsi → disimpan → hanya `ticket_code` yang dikembalikan.
- **Tiket** (`PPKS-XXXXXX-XXXXXX`, 12 digit acak) dipakai pelapor untuk memantau status dan chat dua arah
  tanpa login.
- **Petugas (Satgas)** login JWT untuk melihat dasbor tersensor, mengubah status/urgensi, menugaskan,
  menambah catatan, dan membalas pesan.
- **Override identitas** (darurat) hanya untuk ADMIN dengan PIN; setiap akses dicatat di audit log.
- Jika Gemini gagal, laporan **tetap tersimpan** dengan fallback aman (urgensi `KUNING`, `ai_failed=true`).

---

## 2. Prasyarat

- Python 3.11+
- (Opsional) API key Gemini dan bot Telegram.

## 3. Instalasi

```sh
cd backend
python -m venv .venv

# Windows (PowerShell)
.\.venv\Scripts\Activate.ps1
# Linux/macOS
# source .venv/bin/activate

pip install -r requirements.txt
```

## 4. Generate kunci keamanan

```sh
python generate_keys.py
```

Salin keluarannya ke `.env`:

```sh
cp .env.example .env   # Windows: copy .env.example .env
```

Isi minimal: `ENCRYPTION_KEY`, `HMAC_KEY`, `JWT_SECRET`, `OVERRIDE_PIN`,
`ADMIN_PASSWORD`, `MEMBER_PASSWORD`. `GEMINI_API_KEY` opsional (tanpa itu, triase memakai
fallback lokal).

## 5. Menjalankan

```sh
uvicorn app.main:app --reload
```

- OpenAPI docs: http://localhost:8000/docs
- Privacy: jalankan tanpa access log agar IP tidak tercatat di log server:
  `uvicorn app.main:app --reload --no-access-log`

Saat startup, jika tabel `officers` kosong, sistem membuat 1 ADMIN + 1 ANGGOTA.
Jika password tidak diisi di env, password acak ditampilkan sekali di log.

## 6. Seed data demo

```sh
python seed_demo.py
```

Membuat ~8 laporan fiktif (campuran MERAH/KUNING/HIJAU, 1 terlapor berulang) agar dasbor,
statistik, dan fitur deteksi pola langsung bisa didemokan.

## 7. Menjalankan tes

```sh
pytest -q
```

Tes memakai SQLite sementara dan **meng-mock Gemini** (tidak butuh jaringan).

---

## 8. Struktur proyek

```
backend/
  app/
    main.py         # app, CORS, header keamanan, rate limit, seed startup
    config.py       # pydantic-settings (.env)
    database.py     # engine, session, Base
    models.py       # Report, Attachment, Officer, InternalNote, FollowUpMessage, AuditLog
    schemas.py      # skema Pydantic v2 + enum
    security.py     # Fernet, bcrypt, JWT, tiket, HMAC, guard PIN
    deps.py         # get_db, get_current_officer, require_role
    seed.py         # seed petugas + laporan demo
    help_resources.json
    routers/
      public.py     # endpoint pelapor
      officer.py    # endpoint petugas (JWT)
      auth.py       # login
    services/
      ai.py         # triase Gemini + fallback
      prefilter.py  # redaksi regex lokal
      notifier.py   # webhook Telegram
      files.py      # validasi & sanitasi lampiran
      audit.py      # audit log
  tests/
  generate_keys.py
  seed_demo.py
  requirements.txt
  .env.example
```

---

## 9. Ringkasan endpoint

| Metode | Path | Akses | Keterangan |
|---|---|---|---|
| POST | `/api/reports` | publik | Buat laporan (multipart), balas `ticket_code` |
| GET | `/api/tickets/{code}` | publik | Status + pesan Satgas (tanpa isi laporan) |
| POST | `/api/tickets/{code}/messages` | publik | Balas pesan ke Satgas |
| GET | `/api/help-resources` | publik | Kontak darurat |
| GET | `/api/procedures` | publik | Prosedur pelaporan (tahapan, syarat, kanal, FAQ) |
| GET | `/api/testimonials` | publik | Galeri testimoni anonim (+ paginasi `page`/`limit`) |
| GET | `/api/profile` | publik | Profil Satgas PPKS (tugas, visi-misi, struktur, kontak) |
| POST | `/api/auth/login` | publik | Login petugas → JWT (30 menit) |
| GET | `/api/officer/reports` | JWT | Daftar + filter (`status`,`urgency`,`category`,`location`,`assigned_to`,`q`) + paginasi + `since` |
| GET | `/api/officer/reports/{id}` | JWT | Detail tersensor |
| PATCH | `/api/officer/reports/{id}/status` | JWT | Ubah status |
| PATCH | `/api/officer/reports/{id}/urgency` | JWT | Ubah urgensi (wajib `reason`) |
| PATCH | `/api/officer/reports/{id}/assign` | JWT | Tugaskan petugas |
| POST | `/api/officer/reports/{id}/notes` | JWT | Catatan internal |
| POST | `/api/officer/reports/{id}/messages` | JWT | Kirim pesan ke pelapor |
| GET | `/api/officer/reports/{id}/attachments/{aid}` | JWT | Unduh lampiran tersanitasi |
| POST | `/api/officer/reports/{id}/override` | ADMIN | Buka identitas (PIN) |
| GET | `/api/officer/stream` | JWT | SSE laporan baru |
| GET | `/api/officer/stats` | JWT | Agregat anonim |
| GET | `/api/officer/patterns` | JWT | Deteksi terlapor berulang |
| GET | `/api/officer/audit-logs` | ADMIN | Audit log |
| GET | `/api/officer/officers` | JWT | Daftar petugas (untuk penugasan) |

---

## 10. Contoh `curl`

### a. Buat laporan

```sh
curl -X POST http://localhost:8000/api/reports \
  -F "text=Saya diancam oleh seseorang di kampus tadi malam dan takut keluar kamar." \
  -F "victim_wish=KONSULTASI" \
  -F "contact=081234567890" \
  -F "location=Gedung B Fakultas Teknik" \
  -F "consent=true" \
  -F "files=@bukti.jpg"
# -> {"ticket_code":"PPKS-482913-750264"}
```

### b. Cek tiket & balas pesan

```sh
curl http://localhost:8000/api/tickets/PPKS-482913-750264

curl -X POST http://localhost:8000/api/tickets/PPKS-ABCD-EFGH-JKLM/messages \
  -H "Content-Type: application/json" \
  -d '{"body":"Terima kasih, saya sedang di tempat aman."}'
```

### c. Login petugas

```sh
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"PASSWORD_ANDA"}'
export TOKEN="isi access_token di sini"
```

### d. Dasbor petugas

```sh
curl "http://localhost:8000/api/officer/reports?urgency=MERAH&page=1&limit=20" \
  -H "Authorization: Bearer $TOKEN"

curl http://localhost:8000/api/officer/reports/REPORT_ID \
  -H "Authorization: Bearer $TOKEN"

curl -X PATCH http://localhost:8000/api/officer/reports/REPORT_ID/urgency \
  -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
  -d '{"urgency":"MERAH","reason":"Ada ancaman langsung terhadap korban."}'

curl -X PATCH http://localhost:8000/api/officer/reports/REPORT_ID/status \
  -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
  -d '{"status":"SEDANG_DIDAMPINGI"}'

curl -X POST http://localhost:8000/api/officer/reports/REPORT_ID/notes \
  -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
  -d '{"note":"Sudah dihubungi via pesan tiket."}'
```

### e. Override identitas (ADMIN)

```sh
curl -X POST http://localhost:8000/api/officer/reports/REPORT_ID/override \
  -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
  -d '{"pin":"123456","confirm_pin":"123456","reason":"Korban dalam bahaya dan perlu pendampingan segera."}'
```

### f. Statistik, pola, audit

```sh
curl http://localhost:8000/api/officer/stats -H "Authorization: Bearer $TOKEN"
curl http://localhost:8000/api/officer/patterns -H "Authorization: Bearer $TOKEN"
curl http://localhost:8000/api/officer/audit-logs -H "Authorization: Bearer $TOKEN"
```

### g. SSE (dasbor realtime)

```sh
curl -N http://localhost:8000/api/officer/stream -H "Authorization: Bearer $TOKEN"
# Alternatif polling:
curl "http://localhost:8000/api/officer/reports?since=2026-01-01T00:00:00Z" -H "Authorization: Bearer $TOKEN"
```

---

## 11. Keamanan & privasi

- **Enkripsi**: `original_text` dan `contact` disimpan sebagai ciphertext Fernet. Hanya dibuka
  lewat override (ADMIN + PIN + audit log).
- **Lokasi**: kolom `location` teks biasa berisi lokasi **umum** yang diisi pelapor (mis.
  `Gedung B Fakultas Teknik`), bukan alamat spesifik; tetap dibersihkan prefilter regex. Bisa
  difilter petugas via `GET /api/officer/reports?location=...`.
- **Hash**: password petugas = bcrypt; JWT = HMAC-SHA256; SHA-256 hanya untuk integritas file;
  nama terlapor = HMAC-SHA256 ber-kunci rahasia (bukan teks asli).
- **Tiket**: 12 digit acak dari `secrets` (numeric-only, format `PPKS-XXXXXX-XXXXXX`); 404 seragam untuk
  tiket tidak valid (anti-enumerasi).
- **Lampiran**: validasi magic bytes (bukan ekstensi) & tervalidasi semua sebelum ditulis, maks 5 MB/file
  & 3 file, nama acak, gambar dirender ulang untuk membuang EXIF/GPS (plus batas dimensi anti
  decompression-bomb), PDF dibersihkan metadata (Info/XMP/embedded files/JavaScript), disimpan di luar
  direktori publik. File dibersihkan bila commit DB gagal (cegah orphan).
- **Tanpa jejak pelapor**: tidak ada kolom IP/User-Agent/header di DB; rate limit in-memory saja.
- **Prompt-injection**: teks dibungkus `<laporan>...</laporan>` sebagai data; upaya injeksi
  menaikkan urgensi minimal ke `KUNING`.
- **Notifikasi MERAH**: dikirim ke Telegram tanpa data pribadi; kegagalan tidak menggagalkan simpan.
- **Rate limit**: login 5/menit, ticket 10/menit, laporan & pesan 10/menit (dapat diatur di `.env`).

## 12. Asumsi & keputusan desain

- Database default SQLite; tabel dibuat dengan `create_all` saat startup (untuk hackathon).
  **Untuk produksi gunakan Alembic migration.**
- `create_all` **tidak** mengubah tabel yang sudah ada. Jika skema berubah (mis. penambahan
  kolom `location` atau index baru), hapus `ppks.db` lalu jalankan `python seed_demo.py` lagi.
- Menjalankan dengan `ENVIRONMENT=production` akan **gagal saat startup** bila `JWT_SECRET`
  masih default/kosong atau `ENCRYPTION_KEY`/`HMAC_KEY` kosong (fail-fast).
- `urgency_final` diinisialisasi dari `urgency_ai` dan bisa diubah petugas; `urgency_ai` tetap
  sebagai jejak keputusan AI.
- Notifikasi Telegram dipicu hanya untuk `urgency_final = MERAH`.
- Rata-rata waktu respons dihitung dari `updated_at - created_at` untuk laporan yang sudah
  ditangani (`status != MENUNGGU_TIM`).
- `GET /api/tickets/{code}` hanya menampilkan pesan dari Satgas (bukan pesan pelapor sendiri).
- Lockout PIN override disimpan in-memory per petugas (cocok untuk single-process). Untuk
  multi-worker, ganti dengan penyimpanan bersama (mis. Redis).
- `seed_demo.py` bersifat aditif; jalankan pada database kosong agar tidak ada duplikasi.
