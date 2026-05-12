# Uptime Monitor

A domain availability monitoring service built with **Laravel 13**, **MySQL 8**, **Redis**, and **Docker**.

## Features

- **Auth** — Register, login, logout. All monitoring pages are protected.
- **Domain management** — Add, edit, delete domains with per-domain check settings.
- **Configurable checks** — Set check interval (1–60 min), request timeout, and HTTP method (GET/HEAD).
- **Automatic checks** — Laravel Scheduler runs every minute; dispatches `CheckDomainJob` for all due domains via Redis queues.
- **Check logs** — Every check saves: timestamp, result (UP/DOWN), HTTP status code, response time (ms), error message.
- **Dashboard** — Overview of all domains with live status, response time, last check time.

## Tech Stack

| Layer      | Technology              |
|------------|-------------------------|
| Framework  | Laravel 13 (PHP 8.4)    |
| Database   | MySQL 8.0               |
| Cache/Queue| Redis 7                 |
| Web server | Nginx 1.25              |
| Frontend   | Blade + Tailwind CDN    |
| Container  | Docker Compose          |

## Quick Start (Docker)

### Requirements
- Docker Engine 24+
- Docker Compose v2

### Install

```bash
git clone https://github.com/your/uptime-monitor.git
cd uptime-monitor

# Full setup in one command:
make install
```

Open: **http://localhost:8080**

Demo credentials:
- Email: `demo@example.com`
- Password: `password`

### Manual setup (if no Make)

```bash
docker compose build
docker compose up -d
sleep 5
docker compose exec app composer install
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

## Available Make Commands

```
make up             # Start containers
make down           # Stop containers
make install        # Full first install
make fresh          # Migrate fresh + seed
make shell          # Bash in app container
make logs           # Tail all logs
make check          # Manually trigger domain checks
make test           # Run the test suite
```

## Railway Deployment

This repository includes a production Dockerfile and Railway config.

### What Works Out of the Box

- Dynamic port binding via Railway `PORT`
- Healthcheck endpoint: `/up`
- Auto migration on startup (can be disabled with `SKIP_MIGRATIONS=1`)
- Support for `DATABASE_URL` and `REDIS_URL`
- Redis fallback when not configured:
   - `CACHE_DRIVER=file`
   - `SESSION_DRIVER=file`
   - `QUEUE_CONNECTION=sync`

### Minimal Variables for Railway

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<your-railway-domain>
LOG_CHANNEL=stderr
DB_CONNECTION=mysql
```

Linked services usually provide:

- `DATABASE_URL`
- `REDIS_URL` (optional)

Detailed step-by-step guide is in [RAILWAY_DEPLOYMENT.md](RAILWAY_DEPLOYMENT.md).

### Background Processing in Production

The web container serves HTTP traffic. For continuous checks in production, you should also run:

- queue worker (`php artisan queue:work`)
- scheduler (`php artisan schedule:run` every minute)

On Docker Compose this is already covered by separate containers.



## Architecture

```
Browser → Nginx → PHP-FPM (app)
                       ↓
                    MySQL (users, domains, check_logs)
                    Redis (sessions, cache, queue)
                       ↓
scheduler container → domains:check command → CheckDomainJob → queue worker
```

- **scheduler** container runs `php artisan schedule:run` every 60 seconds
- **queue** container processes `CheckDomainJob` using Guzzle HTTP client
- Results stored in `check_logs`, domain status cached on `domains` table

## Cron Job

Add this to your cron for automatic scheduled checks:

```cron
* * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
```

This runs the Laravel Scheduler every minute, which dispatches `CheckDomainJob` for all due domains via queues.

## Environment Variables

See `.env.example` for full list. Key variables:

```env
APP_URL=http://localhost:8080
DB_DATABASE=uptime_monitor
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
MAIL_MAILER=smtp
```

## Database Schema

```
users          — id, name, email, password
domains        — id, user_id, url, name, is_active, check_method, check_interval, check_timeout,
                 is_up, last_status_code, last_response_time, last_checked_at, status_changed_at
check_logs     — id, domain_id, checked_at, is_up, status_code, response_time, error, check_method
```

## AI Agents (GitHub Copilot)

Two custom Copilot agents are included in `.github/agents/` to assist with development.

### `@Code Reviewer` — Review & fix code

Audits PHP/Laravel files for bugs, security issues, and best practice violations. Read-only until it finds something to fix — no terminal access.

**Example prompts:**
```
@Code Reviewer review app/Http/Controllers/DomainController.php
@Code Reviewer check all models for mass assignment issues
@Code Reviewer audit the auth and authorization layer
@Code Reviewer review app/Jobs/CheckDomainJob.php for error handling issues
```

**What it checks:**
- `$fillable` / `$guarded` on every Eloquent model
- `authorize()` calls on destructive controller actions
- `$request->validate()` on all user input
- Raw queries, hardcoded credentials, missing `findOrFail()`
- OWASP Top 10 (SQL injection, missing auth, mass assignment)

### `@TDD` — Test-driven development

Drives all new behavior through the red → green → refactor cycle. Writes the failing test first, then the minimum production code to pass it, then refactors.

**Example prompts:**
```
@TDD add a test that a user cannot delete a domain they don't own
@TDD write feature tests for DomainController store and destroy
@TDD test that CheckDomainJob dispatches when a domain is created
@TDD test that an inactive domain is skipped during scheduled checks
```

**How it works:**
1. Writes a failing PHPUnit/Pest test
2. Runs `php artisan test` — confirms red
3. Writes minimal production code to go green
4. Runs again — confirms green
5. Refactors if needed, re-confirms green

> Both agents are available in VS Code via the Copilot chat agent picker (`@` in chat).

## Demo

🔗 [https://uptime-monitor.railway.app](https://uptime-monitor.railway.app) *(deploy link)*
