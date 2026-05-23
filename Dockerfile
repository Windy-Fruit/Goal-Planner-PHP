FROM php:8.4-cli

# System dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions required by Laravel + Sanctum
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring zip bcmath

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy the application
COPY . /app

# Install PHP dependencies and prepare the application
RUN composer install --no-interaction --no-progress --prefer-dist --optimize-autoloader \
    && cp .env.example .env \
    && mkdir -p database \
    && touch database/database.sqlite \
    && php artisan key:generate --force \
    && php artisan migrate --force \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
