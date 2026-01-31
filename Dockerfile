# syntax=docker/dockerfile:1
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && a2enmod rewrite \
    && a2dismod mpm_event mpm_worker \
    && a2enmod mpm_prefork \
    && rm -rf /var/lib/apt/lists/*

# Configure Apache to use /public as document root
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri 's!^ErrorLog .*!!' /etc/apache2/sites-available/000-default.conf \
    && sed -ri 's!^CustomLog .*!!' /etc/apache2/sites-available/000-default.conf \
    && printf '\nErrorLog /proc/self/fd/2\nCustomLog /proc/self/fd/1 combined\n' >> /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Copy application
COPY . /var/www/html

# Make start script executable
RUN chmod +x /var/www/html/start.sh

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/public \
    && mkdir -p /var/www/html/logs \
    && chmod -R 755 /var/www/html/logs

# Expose port
EXPOSE 80

# Start Apache (Railway uses $PORT)
CMD ["/var/www/html/start.sh"]
