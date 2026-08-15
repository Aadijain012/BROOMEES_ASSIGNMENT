#!/usr/bin/env sh
set -eu

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views
chown -R www-data:www-data storage bootstrap/cache
sed "s/__PORT__/${PORT:-10000}/g" /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

php artisan migrate --force
php artisan db:seed --force
php artisan optimize:clear

php-fpm -D
exec nginx -g 'daemon off;'
