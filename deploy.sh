#!/bin/bash
set -e

cd /var/www/menut

# Mark directory as safe for git
git config --global --add safe.directory /var/www/menut

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

echo "Deployment complete!"
