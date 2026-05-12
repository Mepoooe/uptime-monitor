#!/bin/bash
set -e

# Use PORT env variable from Railway (default to 8080)
export PORT=${PORT:-8080}

echo "Starting application on port $PORT"

# Update nginx to listen on PORT for Railway deployments.
sed -i "s/PORT_PLACEHOLDER/$PORT/" /etc/nginx/http.d/default.conf

# Create .env if not exists, otherwise update with environment variables
if [ ! -f /app/.env ]; then
    echo "Creating .env from .env.example"
    cp /app/.env.example /app/.env
fi

# If Redis is not configured in Railway, fallback to file/sync drivers.
if [ -z "${REDIS_URL}" ] && [ -z "${REDIS_HOST}" ]; then
    export CACHE_DRIVER=${CACHE_DRIVER:-file}
    export SESSION_DRIVER=${SESSION_DRIVER:-file}
    export QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}
fi

# Apply environment variables to .env (Laravel will read them from env() function)
# These are just for reference, Laravel should read from environment directly

# Generate APP_KEY if not set
if grep -q "^APP_KEY=$" /app/.env; then
    echo "Generating APP_KEY..."
    php /app/artisan key:generate --force 2>/dev/null || true
fi

if [ "${RUN_MIGRATIONS_ON_STARTUP}" = "1" ] && [ -z "$SKIP_MIGRATIONS" ]; then
    echo "Running database migrations..."
    php /app/artisan migrate --force || echo "Migration skipped (database may not be ready)"
fi

if [ "${RUN_CACHE_ON_STARTUP}" = "1" ]; then
    echo "Caching configuration..."
    php /app/artisan config:cache 2>/dev/null || true
    php /app/artisan route:cache 2>/dev/null || true
    php /app/artisan view:cache 2>/dev/null || true
fi

echo "Starting supervisor..."

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
