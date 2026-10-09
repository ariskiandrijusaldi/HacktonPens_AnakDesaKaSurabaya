# Agen Satgas PPKS Kampus — Frontend Blade

Frontend untuk sistem pelaporan kekerasan seksual anonim dengan triase AI. Laravel **hanya menyajikan halaman Blade**; seluruh data diambil langsung dari browser ke REST API FastAPI melalui `fetch`.

> Prioritas proyek ini: berjalan, jelas, dan aman untuk pelapor. UI dirancang menenangkan, tegas, dan menghormati privasi korban.

## Ringkasan Arsitektur

- **Laravel (port 8000)** — hanya route view-only (`routes/web.php`) dan asset Vite.
- **FastAPI (port 8001)** — seluruh API: laporan, tiket, auth, petugas, SSE, statistik, audit.
- **Browser** — memanggil `VITE_API_BASE_URL` secara langsung. Tidak ada proxy/controller perantara di Laravel.
- **Vite + Tailwind CSS v4** — bundle lokal, memakai **font sistem** (tanpa Google Fonts/CDN pihak ketiga).

## Menjalankan

Prasyarat: PHP 8.2+, Composer, Node.js 20+.

```bash
composer install
npm install

# siapkan environment
copy .env.example .env      # Windows
# cp .env.example .env       # macOS/Linux
php artisan key:generate
```

Jalankan dua proses (terminal terpisah):

```bash
npm run dev          # aset Vite (atau: npm run build untuk produksi)
php artisan serve    # default http://127.0.0.1:8000
```

Untuk build produksi:

```bash
npm run build
php artisan serve
```

### Catatan driver database

Frontend ini **tidak membutuhkan database** (tidak ada query di Laravel). Namun `.env.example` bawaan memakai `SESSION_DRIVER=database` dan `CACHE_STORE=database`. Jika ekstensi `pdo_sqlite` tidak tersedia, cukup ubah di `.env` lokal:

```env
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

## Environment

`.env.example` menyertakan variabel frontend berikut:

```env
VITE_API_BASE_URL=http://localhost:8001
VITE_QUICK_EXIT_URL=https://www.google.com
```

- `VITE_API_BASE_URL` — base URL FastAPI. Dibaca di JS via `import.meta.env.VITE_API_BASE_URL`.
- `VITE_QUICK_EXIT_URL` — tujuan tombol **Keluar Cepat** dan pintasan `Esc` dua kali (default Google agar halaman tidak tersisa di riwayat Back).

Tidak ada secret/API key di frontend.

## Konfigurasi FastAPI (Backend)

FastAPI harus berjalan di **port berbeda** dari Laravel (default `8001` vs Laravel `8000`) dan mengizinkan origin Laravel di CORS:

```python
CORS_ORIGINS = [
    "http://localhost:8000",
    "http://127.0.0.1:8000",
]
```

- Izinkan header `Authorization` dan metode `GET, POST, PATCH, OPTIONS`.
- Endpoint SSE (`/api/officer/stream`) harus mengirim `Content-Type: text/event-stream` dan mendukung pembacaan bertahap. SSE dikonsumsi dengan `fetch` + `ReadableStream` (bukan `EventSource`) karena `EventSource` tidak bisa mengirim header `Authorization`.
- Endpoint lampiran (`/api/officer/reports/{id}/attachments/{attachment_id}`) mengembalikan berkas dan membutuhkan token; frontend mengambilnya sebagai blob.

### Contoh header CSP (disarankan)

Tambahkan di level web server / reverse proxy (bukan di kode aplikasi):

```
Content-Security-Policy:
  default-src 'self';
  base-uri 'self';
  form-action 'self';
  img-src 'self' data: blob:;
  font-src 'self';
  style-src 'self' 'unsafe-inline';
  script-src 'self';
  connect-src 'self' http://localhost:8001 http://127.0.0.1:8001;
  frame-ancestors 'none';
  object-src 'none';
```

Sesuaikan `connect-src` dengan URL FastAPI dan tambahkan `img-src blob:` karena lampiran ditampilkan lewat blob URL.

## Peta Halaman

| Route | View | Keterangan |
| --- | --- | --- |
| `/` | `pages/public/landing` | Landing: hero, strip fitur, cara kerja, transparansi |
| `/lapor` | `pages/public/lapor` | Form laporan + konfirmasi kode tiket |
| `/tiket` | `pages/public/tiket` | Cek status + balasan pelapor (polling 15s) |
| `/bantuan` | `pages/public/bantuan` | Kontak darurat + P3 + hak pelapor |
| `/masuk` | `pages/officer/masuk` | Login petugas (JWT) |
| `/dasbor` | `pages/officer/dasbor` | Daftar laporan, filter, real-time, alarm |
| `/dasbor/laporan/{id}` | `pages/officer/laporan` | Detail, status/urgensi/assign, catatan, chat, override |
| `/dasbor/statistik` | `pages/officer/statistik` | Statistik + grafik SVG/CSS |
| `/dasbor/pola` | `pages/officer/pola` | Pengelompokan hash terlapor |
| `/dasbor/audit` | `pages/officer/audit` | Audit log (ADMIN) |

## Struktur Frontend

```
resources/
  css/app.css                  # Tailwind v4 + token desain + komponen
  js/
    app.js                     # bootstrap: quick exit, dropdown, guard petugas
    auth.js                    # JWT di sessionStorage, guard, watch expiry
    api/client.js              # satu apiClient: base URL, token, 401/429/error
    api/public.js              # endpoint publik
    api/officer.js             # endpoint petugas
    lib/{format,enums,toast,copy,text,exit,alarm,sse}.js
    pages/*.js                 # satu modul per halaman
  views/
    layouts/{public,officer}.blade.php
    components/*               # badge, header, footer, skeleton, stepper, quick-exit
    pages/public/*, pages/officer/*
```

### Privasi & keamanan yang diterapkan

- `<meta name="referrer" content="no-referrer">` dan `<meta name="robots" content="noindex, nofollow">` di semua layout.
- Tanpa Google Fonts/CDN/analytics/tracker; font memakai `system-ui`.
- Token JWT hanya di `sessionStorage` (tanpa refresh). Saat `401`: token dihapus, arahkan ke `/masuk?pesan=Sesi berakhir`.
- Tidak ada PII di `localStorage`/`sessionStorage`/`IndexedDB`; draf form hanya di memori.
- Teks laporan & kontak selalu dirender dengan `textContent` (bukan `innerHTML`), dan Blade memakai `{{ }}` (auto-escape) — tidak ada `{!! !!}` untuk data dinamis.
- Tidak ada `console.log` data sensitif. Data sensitif tidak ditaruh di URL/query/`<title>`.
- Kontak asli hanya muncul lewat **Override** oleh ADMIN, itupun hanya sementara di DOM modal dan dihapus saat ditutup (auto-tutup 60 detik).

## Otorisasi

Proteksi halaman petugas dilakukan **di sisi klien** (cek token `sessionStorage`, redirect ke `/masuk` bila tidak ada/kedaluwarsa). **Otorisasi sebenarnya tetap ada di backend FastAPI** (memvalidasi JWT dan peran). Frontend tidak boleh dianggap sebagai batas keamanan.

## Kontrak API yang Dipakai

Enum: `Urgency` (MERAH/KUNING/HIJAU), `VictimWish` (KONSULTASI/PENDAMPINGAN/PROSES_FORMAL/BELUM_TAHU), `Status` (MENUNGGU_TIM/SEDANG_DIDAMPINGI/SELESAI), `Role` (ADMIN/ANGGOTA), `MessageSender` (SATGAS/PELAPOR).

Publik: `POST /api/reports`, `GET /api/tickets/{code}`, `POST /api/tickets/{code}/messages`, `GET /api/help-resources`.
Auth: `POST /api/auth/login`.
Petugas: `/api/officer/reports`, `.../{id}`, `/status`, `/urgency`, `/assign`, `/notes`, `/messages`, `/attachments/{id}`, `/override`, `/stream` (SSE), `/stats`, `/patterns`, `/audit-logs`, `/officers`.

Urutan daftar laporan **tidak diurutkan ulang di klien** — mengikuti urutan backend (MERAH > KUNING > HIJAU, lalu paling lama menunggu).

## Asumsi & Keputusan (opsi paling sederhana yang aman)

1. **Fallback real-time** memuat ulang daftar penuh tiap 5 detik (bukan `since=`), agar tidak ada data lama yang tertinggal saat penggabungan/paginasi. Indikator koneksi menampilkan **Live / Polling / Terputus**.
2. **Alarm MERAH** berbunyi saat event SSE `report` dengan `urgency_final = MERAH` masuk. Saat mode polling, tidak ada event baru sehingga alarm tidak berbunyi — pengguna melihat indikator "Polling".
3. **Penghapusan metadata foto** diasumsikan terjadi di backend/AI (frontend hanya memvalidasi tipe/ukuran dan memberi info). Frontend tidak memodifikasi biner berkas.
4. **Teks UI** Blade ditulis inline dalam Bahasa Indonesia; string dinamis sisi-JS terpusat di `resources/js/lib/text.js`.
5. `Esc` dua kali memicu Keluar Cepat. Saat sebuah modal terbuka, `Esc` menutup modal terlebih dahulu (tidak keluar cepat).
6. Ambang SLA di satu konstanta `SLA_HOURS` (`resources/js/lib/enums.js`): MERAH > 1 jam, KUNING > 24 jam.
7. Halaman dibangun mobile-first untuk publik dan desktop-first untuk petugas; grafik statistik memakai SVG/CSS tanpa library.
8. Login: `totp_code` dikirim hanya bila diisi.

## Kebutuhan Backend

Frontend ini **tidak menambah** controller/model/migration/route API. Hal yang perlu dipastikan di sisi FastAPI:

1. **CORS** mengizinkan origin Laravel (`http://localhost:8000`, `http://127.0.0.1:8000`), header `Authorization`, dan metode `GET/POST/PATCH/OPTIONS`.
2. **SSE** `/api/officer/stream` mengirim event `ping` dan `report` dengan payload `{id, ticket_code, urgency_final, category, summary, created_at}`; mendukung koneksi dari `fetch` (bukan hanya `EventSource`).
3. **Lampiran** dikembalikan sebagai berkas biner dengan `Content-Disposition` yang sesuai; frontend mengunduhnya sebagai blob.
4. **Override** mengembalikan `{report_id, ticket_code, contact, original_text, message}`. Bila `contact` null, frontend menampilkan `message`. PIN salah → `403`; 3x salah → `429` (terkunci 15 menit).
5. **Login** mengembalikan `expires_in` (detik) agar frontend bisa memberi peringatan 2 menit sebelum kedaluwarsa; rate limit `429` saat > 5/menit.
6. **Statistik** menyediakan `by_urgency` dengan `key` bernilai `MERAH/KUNING/HIJAU` dan `by_status` dengan `key` bernilai enum status, agar grafik terisi. Bila salah satu kosong, UI tetap tampil dengan data `0`.
7. **Pola** mengembalikan `ticket_codes` (bukan `id` laporan). Kode tiket ditampilkan sebagai chip, **bukan tautan**, agar kode rahasia pelapor tidak bocor ke URL/riwayat. Agar tiap grup bisa ditautkan langsung ke detail, backend sebaiknya menyertakan `report_id` pada tiap grup (frontend siap memakainya).

## Verifikasi

```bash
npm run build            # memastikan semua entry JS/CSS terkompilasi
php artisan route:list   # memastikan route view-only terdaftar
```

Semua route di atas diuji merender `200` melalui `php artisan serve`.
