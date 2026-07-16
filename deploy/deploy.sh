#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/24hstore-qr-warranty}"
cd "$APP_DIR"

restore_app() { php artisan up >/dev/null 2>&1 || true; }
trap restore_app EXIT

php artisan down --retry=30 || true
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
if [[ -f package-lock.json ]]; then
    npm ci
else
    npm install --no-audit --no-fund
fi
npm run build
php artisan migrate --force
php artisan storage:link || true
php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan up
trap - EXIT
