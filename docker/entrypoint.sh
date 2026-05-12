#!/bin/bash
set -e

export PORT=${PORT:-8080}
echo "Starting application on port $PORT"

sed -i "s/PORT_PLACEHOLDER/$PORT/" /etc/nginx/http.d/default.conf

# Create .env
if [ ! -f /app/.env ]; then
    echo "Creating .env from .env.example"
    cp /app/.env.example /app/.env
fi

# === fill vars RAILWAY in .env ===
update_env() {
    local key=$1
    local value=$2
    if [ -n "$value" ]; then
        if grep -q "^${key}=" /app/.env; then
            sed -i "s|^${key}=.*|${key}=${value}|" /app/.env
        else
            echo "${key}=${value}" >> /app/.env
        fi
    fi
}

update_env "APP_ENV"      "$APP_ENV"
update_env "APP_DEBUG"    "$APP_DEBUG"
update_env "APP_URL"      "$APP_URL"
update_env "DB_CONNECTION" "$DB_CONNECTION"
update_env "DB_HOST"      "$DB_HOST"
update_env "DB_PORT"      "$DB_PORT"
update_env "DB_DATABASE"  "$DB_DATABASE"
update_env "DB_USERNAME"  "$DB_USERNAME"
update_env "DB_PASSWORD"  "$DB_PASSWORD"
update_env "REDIS_HOST"   "$REDIS_HOST"
update_env "REDIS_PORT"   "$REDIS_PORT"
update_env "REDIS_PASSWORD" "$REDIS_PASSWORD"

# Redis fallback
if [ -z "${REDIS_HOST}" ]; then
    update_env "CACHE_DRIVER"    "file"
    update_env "SESSION_DRIVER"  "file"
    update_env "QUEUE_CONNECTION" "sync"
fi

# Generate APP_KEY if not set
if grep -q "^APP_KEY=$" /app/.env || ! grep -q "^APP_KEY=" /app/.env; then
    echo "Generating APP_KEY..."
    php /app/artisan key:generate --force 2>/dev/null || true
fi

if [ "${RUN_MIGRATIONS_ON_STARTUP}" = "1" ]; then
    echo "Running database migrations..."
    php /app/artisan migrate --force || echo "Migration skipped"
fi

if [ "${RUN_CACHE_ON_STARTUP}" = "1" ]; then
    echo "Caching configuration..."
    php /app/artisan config:cache 2>/dev/null || true
    php /app/artisan route:cache 2>/dev/null || true
    php /app/artisan view:cache 2>/dev/null || true
fi

echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf