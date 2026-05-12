# Deployment on Railway

This project is ready to deploy to Railway via Dockerfile.

## What Is Already Configured

- Container reads Railway `PORT` and binds Nginx to it.
- Healthcheck endpoint is `GET /up`.
- Startup runs:
   - `php artisan key:generate` (only if `APP_KEY` is empty)
   - optional startup maintenance only when explicitly enabled
- MySQL and Redis URL variables are supported:
   - `DATABASE_URL`
   - `REDIS_URL`
- If Redis is not configured, app falls back to:
   - `CACHE_DRIVER=file`
   - `SESSION_DRIVER=file`
   - `QUEUE_CONNECTION=sync`

## Railway Setup

1. Create a new Railway project from your GitHub repository.
2. Add a MySQL service and link it to the app service.
3. Optional: add a Redis service and link it to the app service.
4. Deploy using the repository `Dockerfile`.

`railway.toml` already contains:

```toml
[build]
dockerfilePath = "Dockerfile"

[deploy]
port = 8080
healthcheckPath = "/up"
healthcheckTimeout = 60
```

## Required Environment Variables

Set these in Railway app service:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<your-railway-domain>
LOG_CHANNEL=stderr
DB_CONNECTION=mysql
```

Usually auto-provided by linked services:

```env
DATABASE_URL=...
REDIS_URL=... # optional
```

## Migrations

Migrations do not block container startup by default.

If you want the container to run them on boot, set:

```env
RUN_MIGRATIONS_ON_STARTUP=1
```

If you need to skip migrations for a deploy:

```env
SKIP_MIGRATIONS=1
```

Manual run (if needed):

```bash
railway run php artisan migrate --force
```

## Notes About Queue and Scheduler

Web service is enough for UI and manual checks.

For continuous background monitoring in production, also run:

- queue worker (`php artisan queue:work`)
- scheduler trigger (`php artisan schedule:run` every minute)

You can do this with separate Railway services or external cron.

If you want config/route/view cache warming on startup, set:

```env
RUN_CACHE_ON_STARTUP=1
```

## Local Smoke Test Before Deploy

```bash
docker build -t uptime-monitor:local .
docker run --rm -p 18080:8080 \
   -e PORT=8080 \
   -e APP_ENV=production \
   -e APP_DEBUG=false \
   uptime-monitor:local
```

Then open:

- `http://127.0.0.1:18080/up`

Expected response: HTTP 200.