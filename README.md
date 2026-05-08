# Uptime Monitor

A domain availability monitoring service built with **Laravel 12**, **MySQL 8**, **Redis**, and **Docker**.

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
| Framework  | Laravel 12 (PHP 8.3)    |
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
make tinker         # Laravel Tinker
```

## Architecture

```
Browser → Nginx → PHP-FPM (app)
                       ↓
                    MySQL (domains, check_logs)
                    Redis (sessions, cache, queue)
                       ↓
scheduler container → domains:check command → CheckDomainJob → queue worker
```

- **scheduler** container runs `php artisan schedule:run` every 60 seconds
- **queue** container processes `CheckDomainJob` using Guzzle HTTP client
- Results stored in `check_logs`, domain status cached on `domains` table

## Environment Variables

See `.env.example` for full list. Key variables:

```env
APP_URL=http://localhost:8080
DB_DATABASE=uptime_monitor
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

## Database Schema

```
users          — id, name, email, password
domains        — id, user_id, url, name, is_active, check_method, check_interval, check_timeout,
                 is_up, last_status_code, last_response_time, last_checked_at, status_changed_at
check_logs     — id, domain_id, checked_at, is_up, status_code, response_time, error, check_method
```

## Demo

🔗 [https://uptime-monitor.railway.app](https://uptime-monitor.railway.app) *(deploy link)*
