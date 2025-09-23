#!/bin/bash

# Church Dashboard Deployment Script
echo "🚀 Starting deployment for Church Dashboard..."

# Navigate to application directory
cd /home/ploi/dash.jekayode.com

# Discard any local changes to build files
echo "📁 Discarding local build file changes..."
git checkout -- public/build/manifest.json 2>/dev/null || echo "No manifest.json to discard"

# Pull latest changes
echo "📥 Pulling latest changes from git..."
git pull origin main

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Reload PHP-FPM
echo "🔄 Reloading PHP-FPM..."
echo "" | sudo -S service php8.4-fpm reload

# Install Node dependencies and build assets
echo "📦 Installing Node dependencies..."
npm ci

echo "🏗️ Building frontend assets..."
npm run build

# Laravel optimization commands
echo "⚡ Optimizing Laravel application..."
php artisan route:cache
php artisan view:clear

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

echo "🚀 Application deployed successfully!"
echo "✅ Deployment completed at $(date)"
