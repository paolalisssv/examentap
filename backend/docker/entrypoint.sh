#!/bin/sh
set -e

: "${PORT:=80}"

sed -ri "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -ri "s/:80>/:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

if [ ! -L public/storage ]; then
    php artisan storage:link
fi

php artisan config:clear

exec apache2-foreground
