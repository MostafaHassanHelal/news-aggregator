#!/bin/sh
set -e

echo "Starting News Aggregator..."

# Wait for database to be ready
echo "Waiting for database connection..."
while ! php artisan db:monitor --max=1 2>/dev/null; do
    echo "Database not ready, waiting..."
    sleep 2
done

echo "Database connected!"

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Seed database if empty
echo "Seeding sources if needed..."
php artisan db:seed --class=SourceSeeder --force 2>/dev/null || true

# Clear and cache config for production
echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link if not exists
php artisan storage:link 2>/dev/null || true

echo "Application ready!"

# Execute the main command
exec "$@"
