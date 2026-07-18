#!/usr/bin/env bash
set -Eeuo pipefail

APP_DIR="${APP_DIR:-/var/www/24hstore-qr-warranty/backend}"
PHP_BIN="${PHP_BIN:-/usr/bin/php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
YARN_BIN="${YARN_BIN:-yarn}"

cd "$APP_DIR"
if [[ -f vendor/autoload.php ]]; then
    "$PHP_BIN" artisan down --retry=30 || true
fi
trap 'if [[ -f vendor/autoload.php ]]; then "$PHP_BIN" artisan up || true; fi' EXIT

"$COMPOSER_BIN" install --no-dev --prefer-dist --no-interaction --optimize-autoloader
"$YARN_BIN" install --frozen-lockfile --non-interactive --production=false
"$YARN_BIN" build
"$PHP_BIN" artisan migrate --force
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan event:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
"$PHP_BIN" artisan queue:restart
"$PHP_BIN" artisan up
trap - EXIT
