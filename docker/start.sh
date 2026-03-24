#!/bin/bash
set -e

cd /var/www/html

php artisan key:generate --force || true

if [ -n "$DATABASE_URL" ]; then
  echo "DATABASE_URL detected"
fi

php artisan migrate --force || true

apache2-foreground