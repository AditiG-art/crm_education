# Nginx + PHP-FPM 8.2 Container for Railway
FROM php:8.2-fpm-alpine

# Install Nginx, supervisor, and required PHP extensions
RUN apk add --no-cache nginx supervisor \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable mysqli pdo_mysql

# Create necessary directories
RUN mkdir -p /run/nginx /var/log/supervisor /var/www/html

# Copy Nginx & Supervisor configurations
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy application source code
WORKDIR /var/www/html
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose Port 80
EXPOSE 80

# Start Supervisor (manages both Nginx & PHP-FPM processes)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
