#!/bin/sh
set -e

if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force --no-interaction
fi
php artisan migrate --force --no-interaction
php artisan config:cache
php artisan route:cache

exec "$@"
