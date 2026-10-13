# Digital Worker — Hackathon Repo (CampusCare / Satgas PPKS)

Anonymous campus sexual-violence reporting system. The working tree is a messy merge of several half-finished branches — **trust the files on disk, not the READMEs**. UI text is Indonesian.

## Repo state & hazards
- On branch `fitur-ai` (3 commits ahead of `origin/fitur-ai`), plus an uncommitted in-progress officer-dashboard rework — trust the working tree, not HEAD. The old in-progress merge is resolved; commit `e7f4d6a` fixed the Laravel↔FastAPI proxy contract. Other branches: `main`, `fitur-backend`, `fitur-frontend`.
- `backup-perubahan.patch` (untracked) is a leftover combined merge diff.
- `HacktonPens_AnakDesaKaSurabaya/` is an accidental embedded git repo staged as a gitlink (mode 160000, no `.gitmodules`); a fresh clone gets an empty dir. Ignore it.
- Root `README.md` is the stock Laravel README. Endpoint source of truth is `backend/README.md`.

## Three runtimes (don't confuse them)
- **Laravel (repo root):** Blade + Vite + Tailwind v4 on :8000 (`php artisan serve`).
  `routes/web.php` is fully wired to `app/Http/Controllers/*`, which are **server-side proxies**
  to FastAPI via `config('services.campuscare.url')`. Auth = server session (`campuscare_token`,
  `campuscare_user`); middleware aliases `cc.officer` / `cc.admin` in `bootstrap/app.php`.
- **`backend/` FastAPI (the real reporting backend, Python 3.11+):** run from inside `backend/` →
  `uvicorn app.main:app --reload` (default :8000 **collides with Laravel**). On this repo's dev box
  the backend is actually launched with `--port 8001` — trust the running process, not the default,
  and always point `CAMPUSCARE_URL` at the port uvicorn actually bound.
  Routers mount under `/api` (`/api/reports`, `/api/auth/login`, `/api/officer/*` on JWT).
  SQLite `backend/ppks.db`; tables via `create_all` at startup, so schema changes require
  deleting `ppks.db`. Officers `admin`/`anggota` are auto-seeded at startup from
  `ADMIN_PASSWORD`/`MEMBER_PASSWORD` in `backend/.env` (empty → login fails). Tests: `pytest -q`
  (`tests/conftest.py` sets env before any import, monkeypatches Gemini, no network).
- **`ai-python/` (Gemini voice/text prototype):** separate FastAPI, run from inside `ai-python/` →
  `uvicorn api:app --port 8001` (imports `from ai import analyze`, so cwd matters). Its own docs say
  `--port 8001` too — collides with the live backend on this box, so pick a free port.
- `dummy-frontend/index.html` is a static mock.

Root `requirements.txt` / `requirements-python.txt` list **Django** and are stale decoys. Real deps:
`backend/requirements.txt` (FastAPI) and `ai-python/requirements.txt` (google-genai).

## CAMPUSCARE_URL trap
Every backend endpoint lives under `/api` — the suffix is **mandatory**; `config/services.php`
defaults to `http://localhost:8001/api`. The backend port is **not fixed**: uvicorn's default :8000
collides with Laravel's `artisan serve` :8000, so it may be launched on another port. On this repo's
dev box the backend runs on `--port 8001`; if you point `CAMPUSCARE_URL` at the wrong port, every
proxy 404s or times out and the officer login silently loops back to `/masuk`. Verify with
`Get-NetTCPConnection -State Listen -LocalPort *` before assuming a port, and test
`POST {url}/api/auth/login` once before touching anything else.

## Laravel ↔ backend contract (aligned since e7f4d6a)
- Public form uses `description` + `attachments[]`; `ReportController` maps them to multipart
  fields `text` (min 20 chars) + `files`, plus `victim_wish`, `consent`, optional `contact`/`location`
  — matching backend `/api/reports` exactly. Keep these field names in sync.
- Other proxies: `/auth/login` (token+role stored in session), `/tickets/{ticket_code}`,
  `/tickets/{ticket_code}/messages`, `/help-resources`, and `/officer/*` for the dashboard
  (`OfficerController` forwards `session('campuscare_token')` as Bearer).
- Enums must match backend `app/schemas.py`: status `MENUNGGU_TIM`/`SEDANG_DIDAMPINGI`/`SELESAI`,
  urgency `MERAH`/`KUNING`/`HIJAU`, role `ADMIN`/`ANGGOTA`.

## Commands
Laravel (repo root):
```sh
composer install
npm install                 # .npmrc sets ignore-scripts=true
copy .env.example .env
php artisan key:generate
npm run build               # required after editing any Vite input or CSS
php artisan serve           # :8000
php artisan test --filter=Name   # single test; `composer test` = config:clear + artisan test
vendor/bin/pint             # laravel/pint installed; no pint.json → default preset
```
Only example tests exist; no CI, no JS lint/typecheck.

`backend/`:
```sh
python -m venv .venv; .\.venv\Scripts\Activate.ps1   # Windows
pip install -r requirements.txt
python generate_keys.py     # prints keys → put in backend/.env
copy .env.example .env
uvicorn app.main:app --reload
python seed_demo.py         # ~8 demo reports
pytest -q
```

## Frontend conventions
- Vite entries are **explicit** in `vite.config.js` (`resources/js/pages/*.js`); a new page JS must
  be added there. `resources/js/{campuscare.js,auth.js}` and legacy `resources/js/api/*` are not entries.
- Routed views: public `lapor`, `ticket`, `bantuan`, `layouts/{home,masuk}`; officer
  `officer/{landing,laporan,detail,statistik,pola,audit}` (landing/laporan are the wired new
  dashboard views, still untracked). Unwired duplicates: legacy `officer/dasbor.blade.php`, new
  `officer/{dashboard,index}.blade.php` (also untracked), and the whole `pages/{public,officer}/*`
  set — don't delete them yet.
- `resources/js/pages/*.js` are Vite entries, but they are `@vite`'d only by the *unwired*
  `pages/{public,officer}/*` views; the live pages are server-rendered Blade with inline JS /
  `app.js`. Editing a `pages/*.js` file won't change the running pages.
- `layouts/app.blade.php` loads `resources/css/campuscare.css`; `layouts/officer.blade.php` loads
  `app.css` + `app.js`. Quirk: `statistik`/`pola`/`audit` views extend `layouts.app` (public layout).
- Sensitive data (reports, contacts): always `{{ }}` / `textContent`, never `{!! !!}`, never
  `console.log`, never in URLs/query.
- `OfficerController` strips `contact`/`original_text` from ordinary detail responses; only the
  ADMIN-only override endpoint returns them (behind PIN, audit-logged).

## backend/ privacy rules (don't regress)
Original report text and contact are Fernet-encrypted; perpetrator names are HMAC'd (never plaintext);
no IP/User-Agent columns; attachments validated by magic bytes, re-encoded to strip EXIF, and stored
outside the public web root; public endpoints return uniform 404 for bad tickets. Keep
`original_text` / `contact` out of logs.

## OpenCode Configuration
- Provider: `cbn-hackathon` via LiteLLM
- Default model: `qwen3-coder-flash`