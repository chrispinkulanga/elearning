# eLearning Platform - Deployment Guide

## 📋 Pre-Deployment Checklist

### 1. **Environment Configuration**
- [ ] Copy `.env.production.example` to `.env` on your server
- [ ] Update all environment variables with production values
- [ ] Generate new APP_KEY: `php artisan key:generate`
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Update `APP_URL` to your domain
- [ ] Configure database credentials
- [ ] Configure mail server settings
- [ ] Update `SANCTUM_STATEFUL_DOMAINS` with your domain

### 2. **Database Setup**
```bash
php artisan migrate --force
php artisan db:seed # If you have seeders
```

### 3. **Storage & Permissions**
```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 4. **Optimization Commands**
```bash
php artisan config:cache
php artisan view:cache
php artisan event:cache
# Note: Skip route:cache due to Laravel framework compatibility issue
# php artisan route:cache
```

### 5. **Frontend Build**
```bash
cd elearning-frontend
npm install
npm run build
```

## 🔒 Security Checklist

- [ ] Set strong database passwords
- [ ] Enable HTTPS/SSL certificate
- [ ] Set `APP_DEBUG=false`
- [ ] Remove `.env.example` from production
- [ ] Set proper file permissions (755 for directories, 644 for files)
- [ ] Enable CSRF protection
- [ ] Configure rate limiting
- [ ] Set up firewall rules
- [ ] Regular backups configured

## 🌐 Server Requirements

### Minimum Requirements:
- PHP >= 8.1
- MySQL >= 5.7 or MariaDB >= 10.3
- Composer
- Node.js >= 16.x
- NPM >= 8.x

### PHP Extensions:
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- GD or Imagick (for image processing)

## 📁 Files to Provide for Hosting

### Backend (Laravel):
1. **All project files** except:
   - `node_modules/`
   - `vendor/` (run `composer install --optimize-autoloader --no-dev` on server)
   - `.env` (configure on server)
   - `storage/logs/*` (will be regenerated)

2. **Important files**:
   - `composer.json` & `composer.lock`
   - All `app/`, `config/`, `database/`, `routes/` directories
   - `public/` directory (document root)
   - `bootstrap/`, `resources/`

### Frontend (Vue.js):
- **Built files** from `elearning-frontend/dist/` after running `npm run build`
- These go in your public HTML directory or CDN

## 🚀 Deployment Steps

### Option 1: Shared Hosting (cPanel)

1. **Upload Files**:
   - Upload all Laravel files to a directory outside `public_html` (e.g., `/home/username/laravel`)
   - Move contents of `public/` to `public_html/`

2. **Update Paths in `public_html/index.php`**:
   ```php
   require __DIR__.'/../laravel/vendor/autoload.php';
   $app = require_once __DIR__.'/../laravel/bootstrap/app.php';
   ```

3. **Configure Database** via cPanel
4. **Set up Cron Job** for queue workers:
   ```
   * * * * * cd /home/username/laravel && php artisan schedule:run >> /dev/null 2>&1
   ```

### Option 2: VPS/Cloud Server

1. **Clone Repository**:
   ```bash
   git clone your-repo-url /var/www/elearning
   cd /var/www/elearning
   ```

2. **Install Dependencies**:
   ```bash
   composer install --optimize-autoloader --no-dev
   cd elearning-frontend && npm install && npm run build
   ```

3. **Configure Nginx/Apache**:
   - Point document root to `/var/www/elearning/public`
   - Enable rewrite rules

4. **Set up Supervisor** for queue workers:
   ```ini
   [program:elearning-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /var/www/elearning/artisan queue:work --sleep=3 --tries=3
   autostart=true
   autorestart=true
   user=www-data
   numprocs=2
   redirect_stderr=true
   stdout_logfile=/var/www/elearning/storage/logs/worker.log
   ```

## 🧪 Testing After Deployment

### API Endpoints Test:
```bash
# Health check
curl https://yourdomain.com/api/health

# Test authentication
curl -X POST https://yourdomain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

### Database Connection Test:
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

### CRUD Operations Test:
- [ ] Create: Register new user
- [ ] Read: Fetch courses list
- [ ] Update: Update user profile
- [ ] Delete: Delete test data

## 🔧 Common Issues & Solutions

### Issue: 500 Internal Server Error
**Solution**: 
```bash
chmod -R 775 storage bootstrap/cache
php artisan cache:clear
php artisan config:clear
```

### Issue: CORS Errors
**Solution**: Update `config/sanctum.php`:
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'yourdomain.com')),
```

### Issue: Storage Files Not Accessible
**Solution**:
```bash
php artisan storage:link
```

### Issue: Queue Jobs Not Processing
**Solution**: Set up cron job or supervisor for queue workers

## 📊 Monitoring & Maintenance

### Regular Tasks:
- Monitor error logs: `storage/logs/laravel.log`
- Database backups (daily recommended)
- Clear cache periodically: `php artisan cache:clear`
- Update dependencies: `composer update` (test in staging first)

### Performance Optimization:
```bash
php artisan config:cache
php artisan view:cache
php artisan event:cache
# Note: Skip route:cache in current Laravel version
```

## 📞 Support

For deployment issues, check:
1. Laravel logs: `storage/logs/laravel.log`
2. Server error logs
3. Browser console for frontend errors

---

**Last Updated**: December 2025
