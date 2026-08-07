# Nginx + PHP 8.2-FPM Alpine Container for Railway
FROM php:8.2-fpm-alpine

# Install Nginx, gettext (for envsubst), supervisor, and MySQL PHP extensions
RUN apk add --no-cache nginx supervisor gettext \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable mysqli pdo_mysql

# Create runtime directories
RUN mkdir -p /run/nginx /var/log/supervisor /var/www/html /etc/nginx/templates

# Copy Nginx template, supervisor config, and entrypoint
COPY docker/nginx.conf.template /etc/nginx/templates/default.conf.template
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

# Copy source code into web root
WORKDIR /var/www/html
COPY . /var/www/html/

# Set ownership and permissions
RUN chown -R www-data:www-data /var/www/html /run/nginx

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
