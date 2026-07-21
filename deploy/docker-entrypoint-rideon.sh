#!/bin/sh
set -eu

install -d -o www-data -g www-data /var/www/html/storage
install -d -o www-data -g www-data /var/www/html/storage/firebase
install -d -o www-data -g www-data /var/www/html/bootstrap/cache

chown www-data:www-data /var/www/html
chown www-data:www-data /var/www/html/public
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

if [ -e /var/www/html/public/storage ] || [ -L /var/www/html/public/storage ]; then
    chown -R www-data:www-data /var/www/html/public/storage
fi

if [ -f /var/www/html/config/app.php ]; then
    chown www-data:www-data /var/www/html/config/app.php
fi

exec "$@"
