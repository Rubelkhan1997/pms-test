# PMS Test - Setup Guide

Complete setup guide for the Hotel Property Management System.

## Prerequisites

### Required Software

- **PHP 8.3+** with extensions: mbstring, xml, curl, intl, pdo_pgsql, pgsql, zip
- **PostgreSQL 15+** (or MySQL 8.0+ as alternative)
- **Node.js 20+** and npm
- **Composer 2.6+**
- **Git**

### Optional (for Development)

- **Docker & Docker Compose** (for containerized development)
- **Redis 7+** (for caching and queues)
- **VS Code** with recommended extensions

---

## Quick Start (Local Development)

### Step 1: Clone Repository

```bash
git clone <repository-url> pms-test
cd pms-test
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

### Step 3: Install JavaScript Dependencies

```bash
npm install
```

### Step 4: Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure your database:

```env
# PostgreSQL (Recommended)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pms_test
DB_USERNAME=pms_user
DB_PASSWORD=secret

# OR MySQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=pms_test
# DB_USERNAME=root
# DB_PASSWORD=
```

### Step 5: Create Database

**PostgreSQL:**
```bash
createdb pms_test
```

**MySQL:**
```bash
mysql -u root -p -e "CREATE DATABASE pms_test;"
```

### Step 6: Run Migrations and Seeders

```bash
php artisan migrate:fresh --seed
```

### Step 7: Build Frontend Assets

```bash
npm run build
```

### Step 8: Start Development Server

```bash
composer run dev
```

Visit: http://localhost:8000

---

## Docker Setup (Recommended for Consistency)

### Step 1: Start Containers

```bash
docker-compose up -d
```

### Step 2: Install Dependencies Inside Container

```bash
docker-compose exec app composer install
docker-compose exec app npm install
```

### Step 3: Setup Application

```bash
docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate:fresh --seed
docker-compose exec app npm run build
```

Visit: http://localhost:8080

---

## PostgreSQL Setup (Detailed)

### Install PostgreSQL (Ubuntu/Debian)

```bash
sudo apt update
sudo apt install postgresql postgresql-contrib
```

### Create Database and User

```bash
sudo -u postgres psql

CREATE DATABASE pms_test;
CREATE USER pms_user WITH PASSWORD 'secret';
GRANT ALL PRIVILEGES ON DATABASE pms_test TO pms_user;
\q
```

### Enable PostgreSQL Extensions

```bash
psql -U pms_user -d pms_test

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";
\q
```

---

## Testing Setup

### Run All Tests

```bash
composer test
```

### Run Tests with Coverage

```bash
composer test:coverage
```

### Run Specific Test Suites

```bash
composer test:unit    # Unit tests only
composer test:feature # Feature tests only
```

### Run Single Test File

```bash
php artisan test tests/Feature/Modules/FrontDesk/ReservationTest.php
```

---

## Development Tools

### Code Linting

```bash
composer lint        # Auto-fix issues
composer lint:test   # Check without fixing
```

### Static Analysis

```bash
composer analyse     # Run PHPStan
```

### Code Refactoring

```bash
composer refactor         # Auto-refactor code
composer refactor:dry-run # Preview changes
```

### IDE Helper (PHPStorm/VS Code)

```bash
composer ide
```

---

## Default Credentials

After seeding, use these credentials:

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@pms.test | password |
| Hotel Admin | hotel.admin@pms.test | password |
| Front Desk | frontdesk@pms.test | password |
| Housekeeping | housekeeping@pms.test | password |
| POS Cashier | cashier@pms.test | password |
| HR Manager | hr@pms.test | password |

---

## Troubleshooting

### Permission Issues (Linux/Mac)

```bash
sudo chown -R $USER:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Windows (Laragon)

Ensure Laragon is running and Apache/Nginx is pointed to `public` folder.

### Database Connection Error

1. Check database service is running
2. Verify credentials in `.env`
3. Test connection: `psql -h 127.0.0.1 -U pms_user -d pms_test`

### Queue Not Processing

```bash
php artisan queue:restart
php artisan queue:work --tries=3
```

### Cache Issues

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

---

## Environment Variables Reference

### Application

```env
APP_NAME="Hotel PMS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=UTC
```

### Database

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pms_test
DB_USERNAME=pms_user
DB_PASSWORD=secret
```

### Cache & Queue

```env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Mail (Development)

```env
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
```

### Security

```env
SESSION_COOKIE=pms_session
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
```

---

## Next Steps

1. ✅ Review [BEST_PRACTICES.md](./BEST_PRACTICES.md)
2. ✅ Review [DEVELOPMENT_PLAN.md](./DEVELOPMENT_PLAN.md)
3. ✅ Start Week 1 tasks from development plan
4. ✅ Set up your IDE with recommended extensions
5. ✅ Configure debugging tools (Xdebug, Ray)

---

## Support

For issues or questions:
- Check documentation in `/docs`
- Review existing GitHub issues
- Contact development team

---

## Development Checklist

- [ ] PHP 8.3+ installed
- [ ] PostgreSQL 15+ installed and running
- [ ] Node.js 20+ installed
- [ ] Composer installed
- [ ] Repository cloned
- [ ] Dependencies installed (composer & npm)
- [ ] Environment configured
- [ ] Database created and migrated
- [ ] Seeders run
- [ ] Tests passing
- [ ] Development server running

---

**Happy Coding! 🚀**
