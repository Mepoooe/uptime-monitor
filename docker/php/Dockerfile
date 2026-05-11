FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    git curl nginx supervisor \
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

RUN composer install --no-dev --no-interaction --optimize-autoloader

RUN mkdir -p storage/framework/{sessions,views,cache} \
             storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY <<'NGINX_CONF' /etc/nginx/http.d/default.conf
server {
    listen 8080;
    root /app/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
NGINX_CONF

EXPOSE 8080

CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]