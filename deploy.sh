#!/bin/bash
set -e

cd /var/www/menut

# Fix permissions FIRST so deploy user can write to necessary directories
sudo chown -R $USER:www-data /var/www/menut
sudo chmod -R 775 /var/www/menut/storage
sudo chmod -R 775 /var/www/menut/bootstrap/cache

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

# Fix permissions for web server (restore www-data ownership)
sudo chown -R www-data:www-data /var/www/menut/storage
sudo chown -R www-data:www-data /var/www/menut/bootstrap/cache
sudo chown -R www-data:www-data /var/www/menut/database
sudo chmod -R 775 /var/www/menut/storage
sudo chmod -R 775 /var/www/menut/bootstrap/cache
sudo chmod 775 /var/www/menut/database
sudo chmod 664 /var/www/menut/database/database.sqlite

echo "Deployment complete!"
