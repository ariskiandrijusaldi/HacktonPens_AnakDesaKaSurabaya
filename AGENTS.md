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