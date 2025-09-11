#!/bin/bash

# Exit on any error
set -e

echo "🚀 Starting deployment process..."

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;")
echo "🔍 Detected PHP version: $PHP_VERSION"

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --ignore-platform-reqs --optimize-autoloader --no-dev

# Install NPM dependencies and build assets
echo "📦 Installing NPM dependencies..."
npm install --no-fund
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
chmod -R 775 storage/framework/views

# Clear configuration cache
echo "🧹 Clearing configuration cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Run database migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Optimize the application
echo "⚡ Optimizing the application..."
php artisan optimize
php artisan route:cache
php artisan view:cache

# Generate storage link if it doesn't exist
if [ ! -L public/storage ]; then
    echo "🔗 Creating storage link..."
    php artisan storage:link
fi

echo "✅ Deployment process completed successfully!"
echo "🚀 Starting the application..."

# Start the application
exec php-fpm
