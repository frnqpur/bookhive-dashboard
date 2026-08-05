#!/bin/bash
set -e

# Ensure required directories exist
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

# Limit chown to specific directories
chown -R www-data:www-data \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache || true

# Install dependencies if not present
if [ ! -f "vendor/autoload.php" ]; then
    echo "Vendor not found, running composer install..."
    composer install --prefer-dist --no-interaction --no-progress
fi

php artisan config:clear

# APP_KEY must be persistent and not generated on the fly
if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY is empty. Copy .env.docker.example to .env.docker and set a persistent Docker APP_KEY."
    exit 1
fi

if [ "$DOCKER_AUTO_MIGRATE" = "true" ]; then
    echo "Running migrations..."
    php artisan migrate --force
fi

if [ "$DOCKER_AUTO_SEED" = "true" ]; then
    echo "Running seeders..."
    php artisan db:seed --force
fi

exec "$@"
