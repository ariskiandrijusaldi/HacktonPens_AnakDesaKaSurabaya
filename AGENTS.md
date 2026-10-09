# CampusCare Frontend (Laravel 13)

Frontend untuk sistem pelaporan kekerasan seksual anonim. UI: Blade + Vite + Tailwind CSS v4. Real API adalah backend **FastAPI terpisah** (repo/branch lain), bukan di repo ini (`composer.json` hanya menyertakan Laravel). Prasyarat: PHP 8.2+ (`composer.json` minta ^8.3), Composer, Node 20+, npm.

> **WARNING — repo sedang dalam migrasi.** `routes/web.php` + `app/Http/Controllers/*` saat ini mengarah ke **server-side proxy** (Laravel memanggil FastAPI via `Http` ke `config('services.campuscare.url')`). README.md masih mendeskripsikan arsitektur lama ("browser panggil FastAPI langsung via `VITE_API_BASE_URL`"). **Percaya kode, bukan README.** Dua pola view/JS ini hidup berdampingan di working tree; jangan hapus yang "legacy" sampai migrasi selesai.

## Setup & menjalankan

```sh
composer install
npm install            # .npmrc set: ignore-scripts=true
copy .env.example .env
php artisan key:generate

npm run dev            # Vite watcher (atau npm run build utk produksi)
php artisan serve      # http://127.0.0.1:8000  (FastAPI di :8001)
```

Alternatif: `composer setup` (setup penuh + migrate), `composer dev` (= `php artisan dev`).

## Verifikasi / lint / test

```sh
npm run build          # pastikan semua entry JS/CSS terkompilasi
php artisan route:list # route view-only/controller terdaftar
composer test          # = config:clear + artisan test (PHPUnit, sqlite :memory:)
vendor/bin/pint        # formatter PHP (tidak ada pint.json → preset default)
```

- Test tunggal: `php artisan test --filter=...` (PHPUnit via `tests/`; hanya example test saat ini).
- Tidak ada linter/typecheck JS, tidak ada CI (`.github/` kosong).

## Kondisi broken saat ini (wajib dibaca sebelum `php artisan` apa pun)

1. **`bootstrap/app.php` fatal error.** `use Illuminate\Foundation\Configuration\Middleware` diimport 2x (baris 5 & 9), dan pemanggilan `$middleware->alias([...])` berada di top-level padahal `$middleware` hanya tersedia di dalam closure `withMiddleware()`. Semua perintah `php artisan` gagal sampai ini diperbaiki: hapus import kembar, pindahkan `$middleware->alias(['cc.officer' => ..., 'cc.admin' => ...])` ke dalam `->withMiddleware(function (Middleware $middleware) {...})`.
2. **`config('services.campuscare.url')` belum terdefinisi.** Abstract class `AppServiceProvider`/`config/services.php` tidak punya kunci `campuscare`; semua controller memanggilnya. Tambahkan `'campuscare' => ['url' => env('CAMPUSCARE_URL', 'http://localhost:8001')]` di `config/services.php` (+ `.env`).
3. **File/class mismatch:** `app/Http/Controllers/EnsureCampusCareOfficer.php` berisi `class CampusCareController` (nama file tidak cocok dengan class → PSR-4 autoload gagal, route `use App\Http\Controllers\CampusCareController` error). Middleware asli ada di `app/Http/Middleware/EnsureCampusCareOfficer.php` & `EnsureCampusCareAdmin.php`.
4. **`layouts/app.blade.php`** me-link `asset('css/campuscare.css')` yang tidak ada di `public/` (sumber: `resources/css/campurcare.css`, nama typo). Halaman belum ke-styling.
5. **`resources/views/layouts/home.blade.php`** diawali literal fence markdown (```` ```blade ````) — bersihkan.

## Arsitektur view & JS (dua pola)

- **Aktif (proxy server-side):** view `lapor`, `ticket`, `bantuan`, `officer/*`, `layouts/{app,home,masuk}`; dikemudi `app/Http/Controllers/{Report,Ticket,Auth,Officer,CampusCare}Controller.php`. Auth pakai **session server** (`campuscare_token`, `campuscare_user`), token JWT tidak di `sessionStorage` lagi (README lama usang).
- **Legacy (masih ada, tidak diroute):** `resources/views/pages/{public,officer}/*` + `resources/js/pages/*.js` + `resources/js/api/*` (fetch langsung ke `VITE_API_BASE_URL`). Masih terdaftar di `vite.config.js`; jangan dirujuk route baru.

## Konvensi frontend

- Entry Vite dieksplisitkan di `vite.config.js` (daftar `input`). Menambah halaman JS baru = tambah entry di sana.
- Tailwind v4: `@import 'tailwindcss'` + token desain di `@theme` (resources/css/app.css) + `@source '../../storage/framework/views/*.php'` (memindai view terkompilasi).
- Teks UI & komentar dalam Bahasa Indonesia. Jalankan `npm run build` setelah mengubah `resources/css/app.css`.
- Data sensitif (laporan, kontak): selalu `{{ }}`/`textContent`, jangan `{!! !!}`, jangan `console.log`, tidak di URL/query.

## Kontrak backend (FastAPI, port 8001)

CORS harus mengizinkan `http://localhost:8000`; backend mengembalikan `data`/`ticket_code`/`access_token` (+ `expires_in`), rate limit `429`. Detail endpoint & SSE ada di README.md "Kontrak API yang Dipakai" & "Kebutuhan Backend" (masih akurat untuk pola baru; frontend sekarang memanggil via controller proxy).