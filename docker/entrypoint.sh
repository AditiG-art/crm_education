#!/bin/sh
set -e

# Default to 80 if PORT environment variable is not set by Railway
PORT="${PORT:-80}"

echo "[Railway Entrypoint] Setting Nginx to listen on dynamic PORT: $PORT"
sed "s/PORT_PLACEHOLDER/$PORT/g" /etc/nginx/nginx.conf.template > /etc/nginx/http.d/default.conf

echo "[Railway Entrypoint] Starting Nginx & PHP-FPM via Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
