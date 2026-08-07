#!/bin/sh
set -e

# Railway dynamic $PORT environment variable (default: 80)
export PORT="${PORT:-80}"

echo "[Railway Entrypoint] Injecting PORT=${PORT} into Nginx configuration..."
envsubst '$PORT' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf

echo "[Railway Entrypoint] Launching Supervisor (PHP-FPM + Nginx)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
