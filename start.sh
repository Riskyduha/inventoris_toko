#!/usr/bin/env bash
set -e

PORT=${PORT:-80}

# Update Apache to listen on Railway's PORT
echo "Starting Apache on PORT=${PORT}"
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf

# Start Apache
exec apache2-foreground
