# Jarvis Core

Cloud backend platform for the Jarvis ecosystem — custom PHP MVC with JWT auth, REST API, and SQLite/MySQL support.

## Requirements

- PHP 8.1+
- Composer
- SQLite (development) or MySQL (production / InfinityFree)

## Setup

```bash
composer install
cp .env.example .env
composer migrate
composer serve
```

The API will be available at `http://localhost:8080`.

## Deploy to InfinityFree

1. Run `composer install --no-dev --optimize-autoloader` locally.
2. Upload the entire project to `htdocs` via FTP or File Manager.
3. Copy `.env.example` to `.env` on the server and update values:
   - `APP_URL` — your InfinityFree domain (e.g. `https://yoursite.infinityfreeapp.com`)
   - `JWT_SECRET` — a long random secret
   - For MySQL (recommended on InfinityFree): set `DB_CONNECTION=mysql` and MySQL credentials from the InfinityFree control panel
4. Ensure `storage/database` and `storage/logs` are writable (chmod 755 or 775).
5. Run migrations once via SSH/cron or visit after uploading (run `php database/migrate.php` if SSH is available).

The web root uses `index.php` at the project root (InfinityFree `htdocs`). All routes are rewritten through it.

Test: `https://your-domain.infinityfreeapp.com/api/v1/health`

## API Endpoints (v1)

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/health` | Health check |
| POST | `/api/v1/auth/register` | Register user |
| POST | `/api/v1/auth/login` | Login |
| POST | `/api/v1/auth/refresh` | Refresh tokens |
| POST | `/api/v1/auth/logout` | Logout |
| GET | `/api/v1/auth/me` | Current user (Bearer token) |

## Project Structure

```
app/          Application code (Controllers, Models, Services, Core)
config/       Configuration files
database/     Migrations
public/       Web entry point (local dev)
routes/       Route definitions
storage/      Logs and SQLite database
websocket/    WebSocket server (future)
index.php     Shared hosting entry point (InfinityFree)
```

See `JARVIS_CORE_BLUEPRINT.md` for the full roadmap.
