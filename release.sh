#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

echo "🚀 Starting deployment/release process..."

# 1. Pull latest code from repository (optional, uncomment if running on production server)
# echo "📥 Pulling latest code..."
# git pull origin main

# 2. Install/Update PHP Dependencies
echo "📦 Installing Composer dependencies (production mode)..."
composer install --no-dev --optimize-autoloader

# 3. Install/Update Node Dependencies & Compile Assets
echo "📦 Installing NPM dependencies..."
npm install --ignore-scripts

echo "⚡ Compiling assets via Vite for production..."
npm run build

# 4. Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# 5. Clear and Cache Configuration, Routes, Views, and Events
echo "🧹 Optimizing and caching Laravel configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Restart queue worker (if running queue as daemon)
echo "🔄 Restarting queue workers..."
php artisan queue:restart || true

echo "✅ Release successfully compiled and optimized!"
echo "🌐 Your application is now ready for production."
