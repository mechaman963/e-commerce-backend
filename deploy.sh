#!/bin/bash

# Exit on any error
set -e

echo "🚀 Starting deployment process..."

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev

# Install NPM dependencies and build assets
echo "📦 Installing NPM dependencies..."
npm install
npm run build

# Generate application key if not exists
if [ ! -f .env ]; then
    echo "🔑 Copying .env.example to .env..."
    cp .env.example .env
    echo "🔑 Generating application key..."
    php artisan key:generate
fi

# Set proper permissions
echo "🔒 Setting up permissions..."
chmod -R 775 storage bootstrap/cache

# Clear configuration cache
echo "🧹 Clearing configuration cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Optimize the application
echo "⚡ Optimizing the application..."
php artisan optimize
php artisan route:cache
php artisan view:cache

echo "✅ Deployment process completed successfully!"
echo "🚀 Starting the application..."

# Start the application
exec php-fpm
