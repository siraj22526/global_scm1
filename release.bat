@echo off
echo 🚀 Starting deployment/release process for Windows...

:: 1. Install PHP dependencies
echo 📦 Installing Composer dependencies (production mode)...
call composer install --no-dev --optimize-autoloader
if %ERRORLEVEL% neq 0 (
    echo ❌ Composer install failed.
    exit /b %ERRORLEVEL%
)

:: 2. Build assets
echo 📦 Installing NPM dependencies...
call npm install --ignore-scripts
if %ERRORLEVEL% neq 0 (
    echo ❌ NPM install failed.
    exit /b %ERRORLEVEL%
)

echo ⚡ Compiling assets via Vite for production...
call npm run build
if %ERRORLEVEL% neq 0 (
    echo ❌ NPM build failed.
    exit /b %ERRORLEVEL%
)

:: 3. Run database migrations
echo 🗄️ Running database migrations...
call php artisan migrate --force
if %ERRORLEVEL% neq 0 (
    echo ❌ Database migrations failed.
    exit /b %ERRORLEVEL%
)

:: 4. Caching
echo 🧹 Optimizing and caching Laravel configurations...
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache
call php artisan event:cache

:: 5. Restart Queue
echo 🔄 Restarting queue workers...
call php artisan queue:restart

echo ✅ Release successfully compiled and optimized!
echo 🌐 Your application is now ready for production.
