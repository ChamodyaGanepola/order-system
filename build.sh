
# Install required PHP extensions except zip
install-php-extensions gd mbstring pdo pdo_mysql
# Run composer ignoring zip
composer install --optimize-autoloader --no-scripts --no-interaction --ignore-platform-req=ext-zip
# Laravel caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
