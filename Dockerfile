# PHP + Apache Dockerfile for Railway
FROM php:8.2-apache

# Install PHP extensions required for MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite for .htaccess support
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy all project files
COPY . /var/www/html/

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configure Apache to allow .htaccess overrides
RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/crm.conf \
    && a2enconf crm

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
