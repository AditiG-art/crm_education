# Nginx + PHP-FPM 8.2 Container for Railway (Unix Domain Socket + Dynamic $PORT)
FROM php:8.2-fpm-alpine

# Install Nginx, supervisor, and mysql extensions
RUN apk add --no-cache nginx supervisor \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable mysqli pdo_mysql

# Create required runtime directories
RUN mkdir -p /run/nginx /var/log/supervisor /var/www/html /run

# Copy Nginx template, PHP-FPM pool config, supervisor config, and entrypoint
COPY docker/nginx.conf.template /etc/nginx/nginx.conf.template
COPY docker/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

# Copy application source code
WORKDIR /var/www/html
COPY . /var/www/html/

# Set ownership & permissions
RUN chown -R www-data:www-data /var/www/html /run /run/nginx

ENTRYPOINT ["/entrypoint.sh"]
