#!/bin/bash

# Exit on error
set -e

# Wait for database availability (simple check loop could be added here if needed)

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# cache config
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start PHP-FPM
echo "Starting PHP-FPM..."
php-fpm
