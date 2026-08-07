# Use Nginx + PHP-FPM image for Railway
FROM webdevops/php-nginx:8.2

# Set Document Root to /app
ENV WEB_DOCUMENT_ROOT=/app
ENV WEB_DOCUMENT_INDEX=index.php

# Copy project files into container
WORKDIR /app
COPY . /app

# Ensure PHP extensions (mysqli, pdo_mysql) are installed and enabled
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable mysqli pdo_mysql

# Set permissions
RUN chown -R application:application /app

# Expose port 80
EXPOSE 80
