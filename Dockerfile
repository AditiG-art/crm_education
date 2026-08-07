# Official PHP 8.2 with Apache
FROM php:8.2-apache

# Fix Apache MPM conflict error (AH00534: More than one MPM loaded)
RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork

# Install MySQL extensions required for database queries
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable mysqli pdo_mysql

# Enable Apache mod_rewrite for URL routing
RUN a2enmod rewrite

# Set Apache DocumentRoot & permissions
WORKDIR /var/www/html
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose Port 80
EXPOSE 80

# Run Apache in foreground
CMD ["apache2-foreground"]
