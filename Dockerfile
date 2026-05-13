FROM php:8.4-fpm-alpine

# bust cache
ARG CACHE_BUST=2

RUN apk add --no-cache \
    git curl nginx ca-certificates supervisor \
    libpng-dev oniguruma-dev libxml2-dev \
    libzip-dev zip unzip bash

RUN docker-php-ext-install \
    pdo_mysql mbstring exif pcntl bcmath gd zip opcache

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN rm -f /app/.env /app/.env.local /app/.env.production \
    && rm -f /app/bootstrap/cache/*.php

RUN composer install --no-dev --no-interaction --optimize-autoloader

RUN mkdir -p storage/framework/sessions \
             storage/framework/views \
             storage/framework/cache \
             storage/logs \
             bootstrap/cache \
             /var/log/supervisor \
             /etc/supervisor/conf.d \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache public

COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /app/entrypoint.sh

RUN chmod +x /app/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/app/entrypoint.sh"]