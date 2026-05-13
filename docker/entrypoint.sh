#!/bin/bash
set -e

export PORT=${PORT:-9000}
echo "==> Starting on port $PORT"

# 1. Nginx port
sed -i "s/PORT_PLACEHOLDER/$PORT/" /etc/nginx/http.d/default.conf

# 2. Delete any cached config from previous deploys
rm -f /app/.env
rm -f /app/bootstrap/cache/config.php
rm -f /app/bootstrap/cache/routes.php
rm -f /app/bootstrap/cache/routes-v7.php
rm -f /app/bootstrap/cache/services.php
rm -f /app/bootstrap/cache/packages.php

# 3. Write .env from Railway environment variables
cat > /app/.env << ENVEOF
APP_NAME=UptimeMonitor
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_URL=${APP_URL:-https://domain-monitor-a4f8.up.railway.app}

LOG_CHANNEL=stderr
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
SESSION_LIFETIME=120

REDIS_CLIENT=phpredis
REDIS_HOST=${REDIS_HOST}
REDIS_PASSWORD=${REDIS_PASSWORD}
REDIS_PORT=${REDIS_PORT:-6379}

MAIL_MAILER=${MAIL_MAILER:-log}
MAIL_HOST=${MAIL_HOST:-localhost}
MAIL_PORT=${MAIL_PORT:-1025}
MAIL_USERNAME=${MAIL_USERNAME}
MAIL_PASSWORD=${MAIL_PASSWORD}
MAIL_FROM_ADDRESS=noreply@uptime-monitor.app
MAIL_FROM_NAME=UptimeMonitor
ENVEOF

echo "==> .env written"
echo "==> DB: host=${DB_HOST} port=${DB_PORT} db=${DB_DATABASE} user=${DB_USERNAME}"

# 4. Generate APP_KEY if empty
if [ -z "${APP_KEY}" ]; then
    echo "==> Generating APP_KEY..."
    php /app/artisan key:generate --force
fi

# 5. Migrate with timeout so healthcheck doesn't fail
echo "==> Running migrations..."
timeout 25 php /app/artisan migrate --force 2>&1 || echo "==> Migration skipped"

echo "==> Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf