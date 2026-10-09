<<<<<<< HEAD
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
=======
# HacktonPens_AnakDesaKaSurabaya Project

This repository contains a full-stack application for Anak Desa Ka Surabaya, featuring:
- Laravel backend (PHP) for the main web application
- FastAPI backend (Python) for the anonymous reporting system
- Vite frontend (JavaScript/TypeScript)

## Project Structure

### Main Components
- `backend/` - FastAPI Python backend for anonymous reporting system
- `app/` - Laravel PHP backend for main web application  
- `resources/` - Frontend assets and views
- `public/` - Public web assets

## Key Commands

### Backend Setup
```bash
# Laravel backend setup
composer install
php artisan key:generate
php artisan migrate --force
npm install --ignore-scripts
npm run build

# FastAPI backend setup (in backend/ directory)
python -m venv .venv
# Windows: .venv\Scripts\Activate.ps1
# Linux/macOS: source .venv/bin/activate
pip install -r requirements.txt
python generate_keys.py
```

### Running Applications
```bash
# Laravel development server
php artisan serve

# FastAPI development server (in backend/ directory)
uvicorn app.main:app --reload

# Frontend development
npm run dev
```

### Testing
```bash
# Laravel tests
php artisan test

# FastAPI tests (in backend/ directory)
pytest -q
```

## Framework Details

### Laravel (PHP)
- Uses SQLite by default (see config/database.php)
- Session storage in database (SESSION_DRIVER=database)
- Cache storage in database (CACHE_STORE=database)
- Application runs on port 8000 by default (APP_URL=http://localhost:8000)

### FastAPI (Python)
- Uses SQLite by default (DATABASE_URL)
- Requires Python 3.11+
- Uses Fernet encryption for sensitive data
- Implements HMAC for reporter identification
- JWT authentication for officers
- Rate limiting and security measures

## Environment Configuration

### Laravel
- Copy `.env.example` to `.env` and configure database settings
- Default database is SQLite (`DB_CONNECTION=sqlite`)

### FastAPI
- Copy `.env.example` to `.env` and configure encryption keys
- Requires ENCRYPTION_KEY, HMAC_KEY, JWT_SECRET, OVERRIDE_PIN, ADMIN_PASSWORD, MEMBER_PASSWORD
- Optional GEMINI_API_KEY for AI triage functionality

## Development Workflow

1. Start Laravel backend: `php artisan serve`
2. Start FastAPI backend: `uvicorn app.main:app --reload` (in backend/)
3. Start frontend: `npm run dev`
4. Run tests: `php artisan test` or `pytest -q` (in backend/)

## Important Notes

- The Laravel application uses SQLite by default for simplicity
- The FastAPI application implements strong privacy protections for reporters
- Both backends have separate database configurations and setup requirements
- The frontend uses Vite for development and build processes
- FastAPI requires Windows PowerShell activation script: `.venv\Scripts\Activate.ps1` for virtual environment
- FastAPI uses in-memory rate limiting and lockout mechanisms
- FastAPI uses HMAC for reporter anonymity and Fernet for data encryption
- FastAPI stores reports with encrypted original text and contact information
- FastAPI requires specific environment variables for security features to work properly
>>>>>>> origin/fitur-backend
