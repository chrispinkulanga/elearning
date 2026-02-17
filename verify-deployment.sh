#!/bin/bash

# Production Deployment Verification Script
# Run this on your server after deployment

echo "🔍 Checking Laravel Application..."

# Check PHP version
echo "PHP Version:"
php -v | head -n 1

# Check if .env exists
echo ""
echo "Checking .env file:"
if [ -f .env ]; then
    echo "✅ .env file exists"
else
    echo "❌ .env file missing - Please create it from .env.production.example"
    exit 1
fi

# Check required PHP extensions
echo ""
echo "Checking PHP extensions:"
extensions=("bcmath" "ctype" "fileinfo" "json" "mbstring" "openssl" "pdo" "tokenizer" "xml")
for ext in "${extensions[@]}"; do
    if php -m | grep -qi "$ext"; then
        echo "✅ $ext"
    else
        echo "❌ $ext missing"
    fi
done

# Check if composer dependencies are installed
echo ""
echo "Checking Composer dependencies:"
if [ -d vendor ]; then
    echo "✅ Vendor directory exists"
else
    echo "❌ Run: composer install --optimize-autoloader --no-dev"
    exit 1
fi

# Check storage permissions
echo ""
echo "Checking permissions:"
if [ -w storage ]; then
    echo "✅ Storage directory is writable"
else
    echo "❌ Run: chmod -R 775 storage bootstrap/cache"
fi

# Check if storage is linked
echo ""
echo "Checking storage link:"
if [ -L public/storage ]; then
    echo "✅ Storage link exists"
else
    echo "⚠️  Run: php artisan storage:link"
fi

# Test database connection
echo ""
echo "Testing database connection:"
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connected successfully';" 2>&1 | tail -n 1

# Check if migrations are up to date
echo ""
echo "Checking migrations:"
php artisan migrate:status | head -n 5

# Test API endpoint
echo ""
echo "Testing API health:"
if command -v curl &> /dev/null; then
    curl -s -o /dev/null -w "%{http_code}" http://localhost/api/health
    echo ""
else
    echo "⚠️  curl not available for testing"
fi

echo ""
echo "✅ Verification complete!"
echo ""
echo "Next steps:"
echo "1. Run: php artisan config:cache"
echo "2. Run: php artisan route:cache"
echo "3. Run: php artisan optimize"
echo "4. Test in browser: https://yourdomain.com"
