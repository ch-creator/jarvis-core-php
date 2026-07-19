# Jarvis Core

Cloud backend platform for the Jarvis ecosystem — custom PHP MVC with JWT auth, REST API, and SQLite/MySQL support.

## Requirements

- PHP 8.4+
- Composer
- SQLite (development) or MySQL/PostgreSQL (production)

## Setup

```bash
composer install
cp .env.example .env
composer migrate
composer serve
```

The API will be available at `http://localhost:8080`.

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
public/       Web entry point
routes/       Route definitions
storage/      Logs and SQLite database
websocket/    WebSocket server (future)
```

See `JARVIS_CORE_BLUEPRINT.md` for the full roadmap.
