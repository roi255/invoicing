# =============================================================================
# Stage 1 — Frontend assets (Vite + Tailwind)
# =============================================================================
FROM node:22-alpine AS assets

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY vite.config.js .
COPY resources/css resources/css
COPY resources/js  resources/js

RUN npm run build

# =============================================================================
# Stage 2 — PHP dependencies (production only, no dev)
# =============================================================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev

# =============================================================================
# Stage 3 — Production image
# =============================================================================
FROM php:8.3-fpm-alpine

# Install runtime libs, nginx, supervisor, then compile PHP extensions and
# remove the -dev headers to keep the layer as small as possible.
RUN apk add --no-cache \
        nginx \
        supervisor \
        libpng libjpeg-turbo freetype \
        libzip libxml2 icu-libs oniguruma \
    && apk add --no-cache --virtual .build-deps \
        libpng-dev libjpeg-turbo-dev freetype-dev \
        libzip-dev libxml2-dev icu-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql pdo_sqlite \
        mbstring xml dom zip intl gd bcmath pcntl exif opcache \
    && apk del .build-deps \
    && rm -rf /tmp/*

WORKDIR /var/www/html

# Copy application (vendor from composer stage, code from composer stage)
COPY --from=vendor --chown=www-data:www-data /app /var/www/html

# Copy compiled frontend assets
COPY --from=assets --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Copy container config
COPY .docker/nginx.conf      /etc/nginx/nginx.conf
COPY .docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY .docker/php.ini         /usr/local/etc/php/conf.d/99-app.ini
COPY .docker/entrypoint.sh   /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
