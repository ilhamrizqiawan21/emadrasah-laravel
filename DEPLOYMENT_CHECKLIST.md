# 🚀 DEPLOYMENT CHECKLIST - E-MADRASAH v2.0

## ✅ CRITICAL SECURITY FIXES (COMPLETED)

### 1. Authentication & Authorization
- [x] **Rate Limiting Login** - Max 5 attempts per minute per IP
- [x] **Login Activity Logging** - Track successful/failed login attempts
- [x] **Active User Check** - Prevent inactive users from logging in
- [x] **Password Strength** - Minimum 12 characters with mixed case, numbers, symbols
- [x] **Role-Based Access Control (RBAC)** - Middleware for role authorization
- [x] **Prevent Mass Assignment** - Role field protected from manipulation

### 2. File Upload Security
- [x] **MIME Type Validation** - Verify actual file type matches extension
- [x] **File Size Limits** - Configurable via config/app.php
- [x] **Safe Filename Generation** - Using random hex to prevent path traversal
- [x] **Upload Rate Limiting** - Max 10 uploads per minute per user
- [x] **Old File Cleanup** - Delete old files on update/delete

### 3. Environment Configuration
- [x] **APP_DEBUG=false** - Default to false in production
- [x] **LOG_LEVEL=error** - Only log errors in production
- [x] **Session Security** - Secure cookies, HTTP only, SameSite=lax
- [x] **Database Configuration** - MySQL default instead of SQLite

---

## 📋 PRE-DEPLOYMENT CHECKLIST

### Server Requirements
- [ ] PHP 8.3+ installed
- [ ] MySQL/MariaDB database server
- [ ] Web server (Nginx/Apache) with PHP-FPM
- [ ] SSL/TLS certificate (Let's Encrypt recommended)
- [ ] Redis (optional, for better caching)

### Environment Setup
```bash
# 1. Clone repository
git clone <repository-url> /var/www/e-madrasah
cd /var/www/e-madrasah

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Edit .env file with production values:
# - APP_URL=https://your-domain.com
# - DB_DATABASE, DB_USERNAME, DB_PASSWORD
# - MAIL settings
# - APP_DEBUG=false (already set)

# 5. Run migrations
php artisan migrate --force

# 6. Create storage links
php artisan storage:link

# 7. Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Web Server Configuration

#### Nginx Example
```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/e-madrasah/public;

    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.ht {
        deny all;
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
}

# Force HTTPS
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}
```

### Database Backup Strategy
```bash
# Create backup script: /usr/local/bin/backup-e-madrasah.sh
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u root -p'password' e-madrasah > /backups/e-madrasah_$DATE.sql
gzip /backups/e-madrasah_$DATE.sql
find /backups -name "*.sql.gz" -mtime +7 -delete
```

### Monitoring Setup
```bash
# Install supervisor for queue worker
sudo apt install supervisor

# Create config: /etc/supervisor/conf.d/e-madrasah-worker.conf
[program:e-madrasah-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/e-madrasah/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasuser=false
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/e-madrasah/storage/logs/worker.log
stopwaitsecs=3600
```

---

## 🔒 SECURITY HARDENING

### File Permissions
```bash
# Set correct permissions
chown -R www-data:www-data /var/www/e-madrasah
chmod -R 755 /var/www/e-madrasah
chmod -R 775 /var/www/e-madrasah/storage
chmod -R 775 /var/www/e-madrasah/bootstrap/cache
```

### Additional Security Measures
- [ ] Enable Cloudflare or similar CDN/WAF
- [ ] Setup fail2ban for SSH and web protection
- [ ] Configure automatic security updates
- [ ] Disable directory listing
- [ ] Hide PHP version headers
- [ ] Setup Content Security Policy (CSP)
- [ ] Enable HSTS (HTTP Strict Transport Security)

---

## 📊 POST-DEPLOYMENT VERIFICATION

### Test Checklist
- [ ] Login/logout functionality
- [ ] Password reset flow
- [ ] File upload (test with various file types)
- [ ] Role-based access (test with different user roles)
- [ ] Rate limiting (attempt multiple failed logins)
- [ ] PDF export functionality
- [ ] Excel export functionality
- [ ] All CRUD operations for each module
- [ ] Session timeout behavior
- [ ] Error pages display correctly (no stack traces)

### Performance Checks
- [ ] Enable query logging and check for N+1 queries
- [ ] Test page load times (< 2 seconds target)
- [ ] Verify caching is working (config, routes, views)
- [ ] Check database indexes are properly set
- [ ] Monitor memory usage

---

## 🆘 EMERGENCY PROCEDURES

### Rollback Plan
```bash
# If deployment fails, rollback database
php artisan migrate:rollback --step=5

# Restore from backup
gunzip /backups/e-madrasah_YYYYMMDD_HHMMSS.sql.gz
mysql -u root -p e-madrasah < /backups/e-madrasah_YYYYMMDD_HHMMSS.sql
```

### Maintenance Mode
```bash
# Enable maintenance mode
php artisan down

# Perform maintenance
# ...

# Disable maintenance mode
php artisan up
```

---

## 📞 SUPPORT CONTACTS

- **Developer**: [Your Contact]
- **Server Admin**: [Admin Contact]
- **Emergency Hotline**: [Phone Number]

---

**Last Updated**: $(date +%Y-%m-%d)
**Version**: 2.0.0
**Status**: Ready for Production Deployment ✅
