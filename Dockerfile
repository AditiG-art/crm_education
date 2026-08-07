# Official PHP 8.2 with Apache for Railway Deployment
FROM php:8.2-apache

# Install MySQL extensions for PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable mysqli pdo_mysql

# Enable Apache mod_rewrite for URL routing and .htaccess
RUN a2enmod rewrite

# Configure Apache to listen dynamically on Railway's $PORT environment variable
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-available/*.conf

# Copy all project files into Apache document root
WORKDIR /var/www/html
COPY . /var/www/html/

# Set correct web permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port
EXPOSE 80

# Start Apache server in foreground
CMD ["apache2-foreground"]
