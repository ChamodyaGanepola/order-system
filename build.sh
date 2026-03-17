#!/bin/bash

# --- Install required PHP extensions ---
install-php-extensions gd zip ctype curl dom fileinfo filter hash mbstring openssl pcre pdo pdo_mysql session tokenizer xml json opcache

# --- Composer install ---
composer install --optimize-autoloader --no-scripts --no-interaction

# --- Laravel caches ---
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# --- Set storage permissions ---
mkdir -p storage/framework/{sessions,views,cache,testing} storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache

# --- Node dependencies and build ---
npm install
npm run build
