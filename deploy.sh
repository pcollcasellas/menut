#!/bin/bash
set -e

cd /var/www/menut

# Pull latest changes
git fetch origin main
git reset --hard origin/main

# Install/update dependencies
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache config
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart queue workers
sudo supervisorctl restart menut-worker:*

# Fix permissions for web server
chown -R www-data:www-data /var/www/menut/storage
chown -R www-data:www-data /var/www/menut/bootstrap/cache
chown -R www-data:www-data /var/www/menut/database
chmod -R 775 /var/www/menut/storage
chmod -R 775 /var/www/menut/bootstrap/cache
chmod 775 /var/www/menut/database
chmod 664 /var/www/menut/database/database.sqlite

echo "Deployment complete!"
