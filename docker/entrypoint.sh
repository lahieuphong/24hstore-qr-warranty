#!/usr/bin/env bash

set -Eeuo pipefail

mkdir -p \
    bootstrap/cache \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

chown -R www-data:www-data bootstrap/cache storage

migration_succeeded=false

for attempt in 1 2 3 4 5; do
    if php artisan migrate --force; then
        migration_succeeded=true
        break
    fi

    echo "Database is not ready (attempt ${attempt}/5). Retrying in 5 seconds..."
    sleep 5
done

if [ "${migration_succeeded}" != "true" ]; then
    echo "Unable to run database migrations after 5 attempts."
    exit 1
fi

if [ "${RUN_DATABASE_SEEDER:-true}" = "true" ]; then
    php artisan db:seed --force
fi

php artisan optimize

exec "$@"
