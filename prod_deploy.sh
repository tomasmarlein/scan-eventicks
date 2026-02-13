#!/bin/sh
set -e

echo "Deploying application ..."

# Put app in maintenance mode (works across Laravel versions)
(php artisan down) || true

# Check php version
php -v

# Make working tree match repo exactly (no divergent branch issues)
git fetch origin
git checkout main
git reset --hard origin/main
git clean -fd

# Env
cp .env.prod .env

# Install PHP depsg
composer install --no-interaction --prefer-dist --optimize-autoloader

# Build assets (only if you really build on server)
npm ci
npm run build

# Migrate (uncomment if you want it)
php artisan migrate --force

# Laravel housekeeping
php artisan storage:link || true
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan icons:cache

# Back up
php artisan up

echo "Application deployed!"
