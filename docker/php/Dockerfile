# syntax=docker/dockerfile:1

# Base PHP-FPM image
FROM php:8.1-fpm-alpine AS base

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    postgresql-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    freetype-dev \
    oniguruma-dev \
    icu-dev \
    libxml2-dev \
    git \
    unzip \
    bash \
    nodejs \
    npm \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        intl \
        gd \
    && rm -rf /var/cache/apk/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Application stage
FROM base AS app

# Set working directory
WORKDIR /app

# Copy application code
COPY . .

# Install PHP dependencies without dev
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Install Node dependencies and build assets
RUN npm ci --omit=dev && npm run build

# Final production image
FROM php:8.1-fpm-alpine

# Install required PHP extensions only
RUN apk add --no-cache \
    postgresql-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    freetype-dev \
    oniguruma-dev \
    icu-dev \
    libxml2-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        intl \
        gd \
    && rm -rf /var/cache/apk/*

# Set working directory
WORKDIR /var/www/html

# Copy built application from app stage
COPY --from=app /app /var/www/html

# Set permissions (adjust as needed)
RUN chown -R www-data:www-data /var/www/html

# Set user
USER www-data

# Expose FPM port
EXPOSE 9000

# Start php-fpm
CMD ["php-fpm"]
