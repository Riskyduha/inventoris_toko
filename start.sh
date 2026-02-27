#!/usr/bin/env bash
set -e

# Get PORT from env or default to 80
PORT=${PORT:-80}

echo "=================================================="
echo "Starting Inventoris Toko Server"
echo "=================================================="
echo "Port: $PORT"
echo "Date: $(date)"
echo "Working Dir: $(pwd)"
echo "--------------------------------------------------"

# Ensure required directories exist
mkdir -p /app/logs

# Update Nginx configuration with PORT
echo "Configuring Nginx to listen on port $PORT..."
sed -i "s/listen \$PORT;/listen $PORT;/g" /etc/nginx/nginx.conf

# Start PHP-FPM daemon
echo "Starting PHP-FPM..."
php-fpm -D

# Give PHP-FPM a moment to start
sleep 2

# Note: Some minimal base images don't include pgrep/ps.
# If php-fpm failed to start, the container will exit later anyway.
# We avoid hard-failing here due to missing tools.

# Start Nginx in foreground (with daemon mode off so container doesn't exit)
echo "Starting Nginx..."
echo "--------------------------------------------------"
exec nginx -g 'daemon off;'
