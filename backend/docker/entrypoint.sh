#!/bin/sh
set -e

: "${PORT:=80}"

sed -ri "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -ri "s/:80>/:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

if [ -n "${FIREBASE_CREDENTIALS_BASE64:-}" ]; then
    mkdir -p storage/app/firebase
    echo "${FIREBASE_CREDENTIALS_BASE64}" | base64 -d > storage/app/firebase/service-account.json
    export FIREBASE_CREDENTIALS="$(pwd)/storage/app/firebase/service-account.json"
fi

if [ ! -L public/storage ]; then
    php artisan storage:link
fi

php artisan config:clear

exec apache2-foreground
