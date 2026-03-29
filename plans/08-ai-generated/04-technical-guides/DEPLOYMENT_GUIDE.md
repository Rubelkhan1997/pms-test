# 🚀 PMS Deployment Guide

**Version:** 1.0.0  
**Last Updated:** March 19, 2026

---

## 📋 Prerequisites

### Server Requirements
- **PHP:** 8.3+
- **Database:** PostgreSQL 15+
- **Cache:** Redis 7+
- **Web Server:** Nginx/Apache
- **Node.js:** 20+
- **Composer:** 2.6+

### Recommended Specs (Production)
- **CPU:** 4+ cores
- **RAM:** 8GB+
- **Storage:** 100GB+ SSD
- **Bandwidth:** 1Gbps

---

## 🔧 Installation Steps

### 1. Clone Repository
```bash
git clone <repository-url> pms
cd pms
```

### 2. Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```env
APP_NAME="Hotel PMS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pms.yourhotel.com

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=pms_production
DB_USERNAME=pms_user
DB_PASSWORD=strong_password

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_password
```

### 4. Database Setup
```bash
# Create database
createdb pms_production

# Run migrations
php artisan migrate --force

# Seed initial data (optional)
php artisan db:seed --class=DatabaseSeeder
```

### 5. Storage Setup
```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 6. Queue Worker Setup
```bash
# Create systemd service
sudo nano /etc/systemd/system/pms-queue.service
```

```ini
[Unit]
Description=PMS Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/pms
ExecStart=/usr/bin/php /var/www/pms/artisan queue:work --tries=3 --timeout=90
Restart=always

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable pms-queue
sudo systemctl start pms-queue
```

### 7. Scheduler Setup
```bash
# Add to crontab
crontab -e

# Add this line
* * * * * cd /var/www/pms && php artisan schedule:run >> /dev/null 2>&1
```

### 8. Web Server Configuration

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name pms.yourhotel.com;
    root /var/www/pms/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 9. SSL Setup (Let's Encrypt)
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d pms.yourhotel.com
```

### 10. Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## 📊 Monitoring Setup

### 1. Laravel Telescope (Development)
```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

### 2. Error Tracking (Sentry)
```bash
composer require sentry/sentry-laravel
php artisan sentry:test
```

### 3. Uptime Monitoring
```bash
# Add to monitoring service (UptimeRobot, Pingdom, etc.)
https://pms.yourhotel.com/api/health
```

---

## 🔄 Deployment Script

```bash
#!/bin/bash

# PMS Deployment Script

echo "🚀 Starting deployment..."

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Run migrations
php artisan migrate --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue
sudo systemctl restart pms-queue

echo "✅ Deployment complete!"
```

Make executable:
```bash
chmod +x deploy.sh
```

---

## 🧪 Testing Before Deployment

```bash
# Run all tests
php artisan test --parallel

# Run with coverage
php artisan test --coverage

# Check code style
composer lint:test

# Static analysis
composer analyse
```

---

## 📈 Performance Optimization

### 1. OPcache Configuration
```ini
; In php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### 2. Database Optimization
```sql
-- Enable PostgreSQL extensions
CREATE EXTENSION IF NOT EXISTS "pg_trgm";
CREATE EXTENSION IF NOT EXISTS "btree_gin";

-- Analyze tables
ANALYZE;
```

### 3. Redis Configuration
```ini
# In redis.conf
maxmemory 2gb
maxmemory-policy allkeys-lru
```

---

## 🔐 Security Checklist

- [ ] SSL certificate installed
- [ ] APP_DEBUG=false
- [ ] Strong database passwords
- [ ] Firewall configured (UFW/iptables)
- [ ] Regular backups configured
- [ ] Error logging enabled
- [ ] Rate limiting configured
- [ ] CORS configured properly
- [ ] File upload restrictions
- [ ] SQL injection prevention (using Eloquent)

---

## 📞 Support & Maintenance

### Daily Tasks
- [ ] Check error logs
- [ ] Monitor queue workers
- [ ] Check disk space
- [ ] Review sync logs (OTA)

### Weekly Tasks
- [ ] Database backup verification
- [ ] Performance metrics review
- [ ] Security updates
- [ ] Log rotation

### Monthly Tasks
- [ ] Full system backup
- [ ] Security audit
- [ ] Performance optimization
- [ ] Update dependencies

---

## 🆘 Troubleshooting

### Queue Workers Stopped
```bash
sudo systemctl status pms-queue
sudo systemctl restart pms-queue
```

### Database Connection Issues
```bash
php artisan db:show
php artisan migrate:status
```

### Cache Issues
```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
```

### Permission Issues
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 📧 Contact Support

For issues or questions:
- **Documentation:** `/plans/` folder
- **Logs:** `storage/logs/laravel.log`
- **Emergency:** Contact system administrator

---

**🎉 Deployment Complete! Your PMS is now live!**
