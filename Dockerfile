# syntax=docker/dockerfile:1
FROM php:8.2-fpm

# Install system dependencies + Nginx
RUN apt-get update && apt-get install -y \
	nginx \
	libpq-dev \
	&& docker-php-ext-install pdo pdo_pgsql \
	&& rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Copy application
COPY . /app

# Copy Nginx config
COPY nginx.conf /etc/nginx/nginx.conf

# Make start script executable
RUN chmod +x /app/start.sh

# Ensure log directory exists
RUN mkdir -p /app/logs && chmod -R 755 /app/logs

# Expose port (Railway uses PORT)
EXPOSE 80

# Start Nginx + PHP-FPM
CMD ["/app/start.sh"]
