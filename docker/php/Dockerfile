FROM php:8.4-fpm-bookworm

# Build arguments for user permissions (defaults to standard UID/GID 1000)
ARG UID=1000
ARG GID=1000

# Set environment
ENV DEBIAN_FRONTEND=noninteractive
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install basic system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    default-mysql-client \
    gosu \
    && rm -rf /var/lib/apt/lists/*

# Install official PHP extension installer helper
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install PHP extensions required by Laravel
RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache \
    redis

# Install Node.js 22 LTS & npm for asset compilation
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Adjust www-data UID/GID or create user to prevent permission issues on host
RUN if [ ${UID} -ne 33 ]; then \
        usermod -u ${UID} www-data 2>/dev/null || true; \
    fi && \
    if [ ${GID} -ne 33 ]; then \
        groupmod -g ${GID} www-data 2>/dev/null || true; \
    fi

# Set working directory
WORKDIR /var/www/html

# Copy custom php configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy entrypoint script
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
