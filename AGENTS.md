# Digital Worker — Hackathon Repo (CampusCare / Satgas PPKS)

Anonymous campus sexual-violence reporting system. The working tree is a messy merge of
several half-finished branches — **trust the files on disk, not the READMEs**. UI text is Indonesian.

## Repo state & hazards
- On branch `fitur-ai` with an **in-progress merge** of `origin/fitur-backend` (staged `backend/`,
  `dummy-frontend/`, etc.; `AGENTS.md` was unmerged). Other branches: `main`, `fitur-backend`, `fitur-frontend`.
- `backup-perubahan.patch` (untracked) is a leftover combined merge diff.
- `HacktonPens_AnakDesaKaSurabaya/` is an accidental embedded git repo staged as a gitlink
  (mode 160000, no `.gitmodules`); a fresh clone gets an empty dir. Ignore it.
- Root `README.md` is the stock Laravel README; the old split sections of this file were a merge
  conflict and both were stale. Endpoint source of truth is `backend/README.md`.

## Known breakage — fix before running Laravel
- `config/services.php` is **corrupted in HEAD**: a bare PHP array fragment with no `<?php` /
  `return [` header. Every `php artisan *` dies with
  `array_merge(): Argument #2 must be of type array, int given`.
  `php -l config/services.php` falsely says "no syntax errors" because without `<?php` the file is
  parsed as plain text. Restore the header, keeping the existing `gemini` and `campuscare` keys.
- All other `config/*.php` are valid.

## Three runtimes (don't confuse them)
- **Laravel (repo root):** Blade + Vite + Tailwind v4 on :8000. `routes/web.php` is fully wired to
  `app/Http/Controllers/*`, which are **server-side proxies** to FastAPI via
  `config('services.campuscare.url')`. Auth = server session (`campuscare_token`, `campuscare_user`);
  middleware aliases `cc.officer` / `cc.admin` in `bootstrap/app.php`.
- **`backend/` FastAPI (the real reporting backend, Python 3.11+):** run from inside `backend/` →
  `uvicorn app.main:app --reload`. Default uvicorn :8000 **collides with Laravel** — pick one.
  Routers mount under `/api` (`/api/reports`, `/api/auth/login`, `/api/officer/*`). SQLite `ppks.db`;
  tables via `create_all` at startup, so schema changes require deleting `ppks.db`. Tests: `pytest -q`
  (mock Gemini, no network).
- **`ai-python/` (Gemini voice/text prototype):** separate FastAPI, run from inside `ai-python/` →
  `uvicorn api:app --port 8001` (imports `from ai import analyze`, so cwd matters).
- `dummy-frontend/index.html` is a static mock.

Root `requirements.txt` / `requirements-python.txt` list **Django** and are stale decoys. Real deps:
`backend/requirements.txt` (FastAPI) and `ai-python/requirements.txt` (google-genai).

## Laravel ↔ backend contract does NOT match
Verify before assuming the proxy works:
- Laravel calls `{CAMPUSCARE_URL}/reports`, `/officer/login`, `/help-resources`; backend serves
  `/api/reports`, `/api/auth/login`, `/api/help-resources`. `CAMPUSCARE_URL` needs the `/api` suffix
  (the default `http://localhost:8001` is wrong twice over).
- Report payload differs: Laravel sends `description` + `attachments[]`; backend expects `text`
  (min 20 chars) + `files`, plus `victim_wish`, `consent`, optional `contact`/`location`.

## Commands
Laravel (root):
```sh
composer install
npm install                 # .npmrc sets ignore-scripts=true
copy .env.example .env
php artisan key:generate
npm run build               # required after editing any Vite input or CSS
php artisan serve           # :8000
php artisan test --filter=Name   # single test; `composer test` = config:clear + artisan test
vendor/bin/pint             # no pint.json → default preset
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
- Vite entries are **explicit** in `vite.config.js`; a new page JS must be added there.
  `resources/js/{campuscare.js,auth.js}` exist but are not entries.
- Active views: `resources/views/{lapor,ticket,bantuan}.blade.php`, `layouts/*`, `officer/*`.
  A duplicate legacy set `resources/views/pages/{public,officer}/*` + `resources/js/api/*`
  (direct fetch to `VITE_API_BASE_URL`) is not routed — don't delete it yet.
- `layouts/app.blade.php` loads `resources/css/campuscare.css` (public pages); other layouts load
  `app.css` + `app.js`.
- Sensitive data (reports, contacts): always `{{ }}` / `textContent`, never `{!! !!}`, never
  `console.log`, never in URLs/query.

## backend/ privacy rules (don't regress)
Original report text and contact are Fernet-encrypted; perpetrator names are HMAC'd (never plaintext);
no IP/User-Agent columns; attachments validated by magic bytes, re-encoded to strip EXIF, and stored
outside the public web root; public endpoints return uniform 404 for bad tickets. Keep
`original_text` / `contact` out of logs.

## Instruction files
- `CLAUDE.md`: stock Laravel Boost bootstrap; Boost is not installed — ignore it.
- `opencode.json`: local OpenCode config (provider `cbn-hackathon` via LiteLLM, default `qwen3-coder-flash`).