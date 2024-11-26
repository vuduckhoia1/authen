# Stage 1: Base image with PHP and development tools
FROM php:8.2-fpm as base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    libpq-dev \
    nodejs \
    npm \
    && docker-php-ext-install \
    intl \
    pdo_mysql \
    opcache \
    && docker-php-ext-enable opcache

# Install Xdebug (for debugging)
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install Composer globally
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Install Symfony CLI globally
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Set working directory
WORKDIR /app

# Copy application source
COPY . .

# Permissions for development
RUN chown -R www-data:www-data /app

# Expose PHP-FPM port
# EXPOSE 9000

# Expose XDebug port
EXPOSE 9003

# Expose Symfony local server port
EXPOSE 8000

CMD ["php-fpm"]

# Stage 2: Nginx for serving the app
FROM nginx:alpine as nginx

# Copy Nginx configuration
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

# Copy application to Nginx container
COPY --from=base /app /var/www/html

# Expose HTTP port
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
