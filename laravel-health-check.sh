#!/bin/bash

if [ "$(id -u)" -eq 0 ]; then
    echo "Refusing to run Laravel Artisan as root."
    echo "Use the application user, for example: docker exec --user www-data rideon-app-1 ./laravel-health-check.sh"
    exit 1
fi

echo "🚀 Running Laravel Health Check..."

# 1. Laravel version
echo -e "\n📌 Laravel Version:"
php artisan --version || { echo "❌ Laravel not installed properly"; exit 1; }

# 2. Environment
echo -e "\n📌 Environment:"
php artisan env || { echo "❌ .env file issue"; exit 1; }

# 3. Routes
echo -e "\n📌 Routes:"
php artisan route:list || { echo "❌ Route issue"; exit 1; }

# 4. Database connection
echo -e "\n📌 Database Migrations Status:"
php artisan migrate:status || { echo "❌ Database connection issue"; exit 1; }

# 5. Clear caches
echo -e "\n📌 Clearing cache & config..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize

# 6. Run tests (if exist)
echo -e "\n📌 Running Tests:"
php artisan test || echo "⚠️ No tests found or some tests failed"

# 7. (Optional) Laravel Pint
if [ -f "./vendor/bin/pint" ]; then
    echo -e "\n📌 Running Pint (code style check)..."
    ./vendor/bin/pint
else
    echo -e "\n⚠️ Laravel Pint not installed. Run 'composer require laravel/pint --dev' to enable code style checks."
fi

echo -e "\n✅ Laravel health check completed!"
