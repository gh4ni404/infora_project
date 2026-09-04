#!/usr/bin/env bash
set -e

# Copy .env if not present
if [ ! -f /var/www/html/.env ]; then
    if [ -f /var/www/html/.env.docker.example ]; then
        cp /var/www/html/.env.docker.example /var/www/html/.env
        echo "Created .env from .env.docker.example"
    elif [ -f /var/www/html/.env.example ]; then
        cp /var/www/html/.env.example /var/www/html/.env
        echo "Created .env from .env.example"
    fi
fi

# Ensure storage and bootstrap/cache directories exist
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Fix permissions for storage and cache directories
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Install composer dependencies if missing
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "vendor/autoload.php not found. Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Generate application key if not set
if grep -q "APP_KEY=$" /var/www/html/.env 2>/dev/null || grep -q "APP_KEY=\"\"$" /var/www/html/.env 2>/dev/null; then
    echo "Generating application key..."
    php artisan key:generate --force --no-interaction
fi

exec "$@"
