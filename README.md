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

## Deploy to Vercel

This project includes Vercel PHP runtime support via `vercel-php`.

1. Push to GitHub and import the repo in [Vercel](https://vercel.com).
2. Set **Framework Preset** to **Other**.
3. Leave **Build Command** empty (Composer runs via `installCommand` in `vercel.json`).
4. Leave **Output Directory** empty — do **not** set it to `public`.
5. Set these environment variables in the Vercel dashboard:

| Variable | Value |
|----------|-------|
| `JWT_SECRET` | A long random secret string |
| `APP_URL` | `https://your-project.vercel.app` |

4. Deploy. Vercel runs `composer install` automatically.

On Vercel, SQLite uses `/tmp/jarvis.sqlite` and migrations run automatically on first request.

> **Note:** SQLite on Vercel is ephemeral (data resets between cold starts). Use MySQL/PostgreSQL for persistent production data.

> **If the site downloads a PHP file:** your Vercel **Output Directory** is likely set to `public`. Clear it, redeploy, and visit `/api/v1/health`.

Test the Vercel entry point locally:

```bash
composer install
cp .env.example .env
composer serve:vercel
```

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
api/          Vercel serverless entry point
config/       Configuration files
database/     Migrations
public/       Local web entry point
routes/       Route definitions
storage/      Logs and SQLite database (local)
websocket/    WebSocket server (future)
```

See `JARVIS_CORE_BLUEPRINT.md` for the full roadmap.
