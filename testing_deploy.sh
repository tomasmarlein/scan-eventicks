#!/bin/sh
set -e

BRANCH="testing"

echo "Deploying application ($BRANCH) ..."

# Maintenance mode (werkt op alle Laravel versies)
(php artisan down) || true

php -v

# Server exact gelijk zetten aan origin/testing
git fetch origin
git checkout "$BRANCH"
git reset --hard "origin/$BRANCH"
git clean -fd

cp .env.testing .env

composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

npm ci
npm run build

php artisan migrate --force

php artisan storage:link || true
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan icons:cache

php artisan up

echo "Application deployed!"
