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
if [ -f /var/www/html/.env ]; then
    chown www-data:www-data /var/www/html/.env 2>/dev/null || true
    chmod 664 /var/www/html/.env 2>/dev/null || true
fi

# Install composer dependencies if missing
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "vendor/autoload.php not found. Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Ensure vendor directory is owned by www-data
if [ -d /var/www/html/vendor ]; then
    chown -R www-data:www-data /var/www/html/vendor /var/www/html/composer.lock 2>/dev/null || true
fi

# Generate application key if not set
if grep -q "APP_KEY=$" /var/www/html/.env 2>/dev/null || grep -q "APP_KEY=\"\"$" /var/www/html/.env 2>/dev/null; then
    echo "Generating application key..."
    php artisan key:generate --force --no-interaction
    chown www-data:www-data /var/www/html/.env 2>/dev/null || true
fi

# Ensure public storage source directory exists
mkdir -p /var/www/html/storage/app/public

# Create relative public storage symlink if not exists
if [ ! -L /var/www/html/public/storage ]; then
    echo "Creating storage symlink (relative)..."
    rm -rf /var/www/html/public/storage 2>/dev/null || true
    ln -sfn ../storage/app/public /var/www/html/public/storage
    chown -h www-data:www-data /var/www/html/public/storage 2>/dev/null || true
fi

# Final check: ensure all storage and cache files are owned and writable by www-data
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
if [ -f /var/www/html/storage/logs/laravel.log ]; then
    chmod 664 /var/www/html/storage/logs/laravel.log 2>/dev/null || true
fi

exec "$@"
