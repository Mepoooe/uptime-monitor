#!/bin/bash
set -e

# Railway provides PORT dynamically — never hardcode 8080 as final
export PORT=${PORT:-8080}
echo "Starting application on port $PORT"

# --- NGINX PORT ---
sed -i "s/PORT_PLACEHOLDER/$PORT/" /etc/nginx/http.d/default.conf

# --- ALWAYS recreate .env from scratch using environment variables ---
# Do NOT rely on .env file from image — Railway vars must win
echo "Writing .env from environment variables..."

cat > /app/.env << EOF
APP_NAME="${APP_NAME:-Laravel}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY:-}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost}"

LOG_CHANNEL=stderr
LOG_LEVEL=debug

DB_CONNECTION="${DB_CONNECTION:-mysql}"
DB_HOST="${DB_HOST:-}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-}"
DB_USERNAME="${DB_USERNAME:-}"
DB_PASSWORD="${DB_PASSWORD:-}"

BROADCAST_DRIVER=log
CACHE_DRIVER="${CACHE_DRIVER:-file}"
FILESYSTEM_DISK=local
QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"
SESSION_DRIVER="${SESSION_DRIVER:-file}"
SESSION_LIFETIME=120

REDIS_HOST="${REDIS_HOST:-}"
REDIS_PASSWORD="${REDIS_PASSWORD:-null}"
REDIS_PORT="${REDIS_PORT:-6379}"

MAIL_MAILER=log
EOF

# If Redis is available — use it
if [ -n "${REDIS_HOST}" ]; then
    sed -i "s/CACHE_DRIVER=.*/CACHE_DRIVER=redis/" /app/.env
    sed -i "s/SESSION_DRIVER=.*/SESSION_DRIVER=redis/" /app/.env
    sed -i "s/QUEUE_CONNECTION=.*/QUEUE_CONNECTION=redis/" /app/.env
fi

# Generate APP_KEY if missing
if grep -q 'APP_KEY=""' /app/.env || grep -q "^APP_KEY=$" /app/.env; then
    echo "Generating APP_KEY..."
    php /app/artisan key:generate --force
fi

# Debug: show DB config (no password)
echo "DB_HOST=${DB_HOST} DB_PORT=${DB_PORT} DB_DATABASE=${DB_DATABASE} DB_USERNAME=${DB_USERNAME}"

# Run migrations
if [ "${RUN_MIGRATIONS_ON_STARTUP}" = "1" ]; then
    echo "Running migrations..."
    php /app/artisan migrate --force || echo "Migration failed — check DB connection"
fi

# Cache config/routes/views for production
echo "Caching..."
php /app/artisan config:cache 2>/dev/null || true
php /app/artisan route:cache 2>/dev/null || true
php /app/artisan view:cache 2>/dev/null || true

echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf