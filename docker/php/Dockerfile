FROM composer:2 AS composer

FROM php:8.3-fpm-alpine

RUN apk add --no-cache postgresql-dev libzip-dev oniguruma-dev $PHPIZE_DEPS \
    && docker-php-ext-install pdo_pgsql mbstring zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

COPY --from=composer /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

COPY . .
RUN chown -R www-data:www-data storage bootstrap/cache

CMD ["php-fpm"]
