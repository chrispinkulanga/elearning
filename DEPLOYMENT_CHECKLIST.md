# 🚀 Pre-Deployment Checklist

Use this checklist before deploying your Laravel application to production to ensure everything works correctly.

## 📋 Pre-Deployment Steps

### 1. **Code Quality Checks**

```bash
# Check for PSR-4 autoloading issues
composer dump-autoload

# Run the pre-deployment validation script
php pre-deployment-check.php

# Check for syntax errors
php -l app/**/*.php

# Run tests (if available)
php artisan test
```

### 2. **Environment Configuration**

- [ ] Copy `.env.example` to `.env` (if not exists)
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY`: `php artisan key:generate`
- [ ] Configure database credentials
- [ ] Configure mail settings
- [ ] Set proper `APP_URL`
- [ ] Configure file storage (local/S3/etc.)

### 3. **Dependencies**

```bash
# Install production dependencies (no dev packages)
composer install --no-dev --optimize-autoloader

# Clear and cache configuration
php artisan config:clear
php artisan config:cache

# Cache routes
php artisan route:clear
php artisan route:cache

# Cache views
php artisan view:clear
php artisan view:cache

# Optimize for production
php artisan optimize
```

### 4. **Database**

```bash
# Run migrations
php artisan migrate --force

# Seed database (if needed, usually skip in production)
# php artisan db:seed --class=ProductionSeeder
```

### 5. **Storage & Permissions**

```bash
# Create storage directories if they don't exist
mkdir -p storage/app/public
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set proper permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Create symbolic link for public storage
php artisan storage:link
```

### 6. **Security**

- [ ] Remove `.env.example` or ensure it doesn't contain sensitive data
- [ ] Ensure `.env` is in `.gitignore`
- [ ] Check that sensitive files are not publicly accessible
- [ ] Review `config/app.php` for security settings
- [ ] Enable HTTPS in production
- [ ] Set secure session configuration
- [ ] Review CORS settings if using API

### 7. **Performance Optimization**

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache everything for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize autoloader
composer dump-autoload --optimize --classmap-authoritative
```

### 8. **Server Requirements**

Verify your server has:
- [ ] PHP 8.2+ (check: `php -v`)
- [ ] Required PHP extensions:
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath
  - Fileinfo
- [ ] Composer installed
- [ ] Node.js & NPM (for frontend)
- [ ] Web server (Apache/Nginx) configured
- [ ] Database server running

### 9. **Frontend Build** (if applicable)

```bash
cd elearning-frontend

# Install dependencies
npm install

# Build for production
npm run build

# The built files should be in dist/ directory
```

### 10. **Final Verification**

```bash
# Run the pre-deployment check script
php pre-deployment-check.php

# Test the application
# - Visit the homepage
# - Test login/registration
# - Test critical features
# - Check error logs: storage/logs/laravel.log
```

## 🔧 Common Issues & Solutions

### PSR-4 Autoloading Errors

**Problem:** Classes not found or namespace mismatches

**Solution:**
```bash
# Fix namespace issues in files
# Ensure class name matches file name
# Ensure namespace matches directory structure

# Regenerate autoload
composer dump-autoload -o
```

### Storage Permission Errors

**Problem:** Can't write to storage directories

**Solution:**
```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Windows (usually not needed, but if issues occur)
# Ensure IIS_IUSRS or your user has write permissions
```

### Database Connection Errors

**Problem:** Can't connect to database

**Solution:**
- Verify database credentials in `.env`
- Ensure database server is running
- Check database user permissions
- Verify network/firewall settings

### Missing Environment Variables

**Problem:** Application errors about missing config

**Solution:**
```bash
# Check .env file exists
# Verify all required variables are set
# Run: php artisan config:clear && php artisan config:cache
```

## 📝 Deployment Commands Summary

```bash
# Complete deployment script (run in order)
composer install --no-dev --optimize-autoloader
composer dump-autoload -o
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan storage:link
php artisan optimize
php pre-deployment-check.php
```

## 🎯 Quick Validation

After deployment, verify:

1. ✅ Homepage loads
2. ✅ No errors in browser console
3. ✅ Database queries work
4. ✅ File uploads work (if applicable)
5. ✅ Email sending works (if applicable)
6. ✅ Authentication works
7. ✅ API endpoints respond correctly
8. ✅ Logs are being written (check `storage/logs/laravel.log`)

## 📞 Support

If you encounter issues:
1. Check `storage/logs/laravel.log` for errors
2. Enable `APP_DEBUG=true` temporarily (remember to disable after)
3. Check server error logs
4. Verify all environment variables are set correctly

---

**Remember:** Always test in a staging environment that mirrors production before deploying!

