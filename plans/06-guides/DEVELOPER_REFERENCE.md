# Developer Quick Reference

## Daily Development Workflow

### Start Development
```bash
# Terminal 1: Start all services
composer run dev

# Or manually:
php artisan serve              # Server
php artisan queue:work         # Queue worker
npm run dev                    # Vite (frontend)
```

### Before Committing
```bash
composer lint:test            # Check code style
composer analyse              # Static analysis
composer test                 # Run tests
```

### After Pulling Changes
```bash
composer install              # Update dependencies
npm install                   # Update JS dependencies
php artisan migrate           # Run new migrations
npm run build                 # Build assets
```

---

## Common Commands Cheat Sheet

### Database
```bash
php artisan migrate                    # Run migrations
php artisan migrate:fresh              # Drop & re-run migrations
php artisan migrate:fresh --seed       # + seed data
php artisan db:seed                    # Run seeders
php artisan tinker                     # Laravel REPL
```

### Cache
```bash
php artisan config:clear               # Clear config cache
php artisan cache:clear                # Clear application cache
php artisan view:clear                 # Clear compiled views
php artisan route:clear                # Clear route cache
php artisan optimize:clear             # Clear all caches
```

### Testing
```bash
php artisan test                                    # Run all tests
php artisan test --filter=testName                  # Run specific test
php artisan test --testsuite=Feature                # Feature tests only
php artisan test --coverage                         # With coverage
./vendor/bin/pest --stop-on-failure                 # Stop on first failure
```

### Code Quality
```bash
composer lint                    # Auto-fix code style (Pint)
composer analyse                 # Static analysis (PHPStan)
composer refactor                # Auto-refactor (Rector)
composer refactor:dry-run        # Preview refactoring
```

### Queue & Jobs
```bash
php artisan queue:work                 # Process queue
php artisan queue:restart              # Restart queue worker
php artisan queue:failed               # List failed jobs
php artisan queue:retry --all          # Retry all failed jobs
php artisan queue:flush                # Flush failed jobs
```

### Generate Components
```bash
php artisan make:model ModelName -mfsr     # Model + migration, factory, seeder, resource
php artisan make:controller ControllerName  # Controller
php artisan make:request RequestName        # Form request
php artisan make:event EventName            # Event
php artisan make:listener ListenerName      # Listener
php artisan make:job JobName                # Job
php artisan make:notification NotifyName    # Notification
php artisan make:policy PolicyName          # Policy
php artisan make:trait TraitName            # Trait
```

---

## Debugging Tools

### Laravel Debugbar (Development)
```bash
composer require barryvdh/laravel-debugbar --dev
```
Access debug bar at bottom of pages.

### Laravel Ray (Best for debugging)
```bash
composer require spatie/laravel-ray --dev
```

**Usage:**
```php
use Spatie\LaravelRay\Ray;

// Basic debugging
ray($variable);

// With label
ray($variable)->label('User Data');

// With color
ray($variable)->green();
ray($variable)->red();
ray($variable)->blue();

// Show queries
ray()->showQueries();

// Measure performance
ray()->measure(function() {
    // Code to measure
});

// Track events
ray()->event('user.created', ['user_id' => 1]);
```

### Xdebug Configuration
Add to `.env` or `php.ini`:
```ini
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_host=127.0.0.1
xdebug.client_port=9003
xdebug.log=/tmp/xdebug.log
```

---

## PostgreSQL Tips

### Connect to Database
```bash
psql -h 127.0.0.1 -U pms_user -d pms_test
```

### Useful Queries
```sql
-- List all tables
\dt

-- Describe table
\d reservations

-- Show indexes
SELECT * FROM pg_indexes WHERE tablename = 'reservations';

-- Explain query performance
EXPLAIN ANALYZE SELECT * FROM reservations WHERE hotel_id = 1;

-- Find slow queries
SELECT query, calls, total_time, mean_time
FROM pg_stat_statements
ORDER BY mean_time DESC
LIMIT 10;

-- Check table size
SELECT 
    relname AS table_name,
    pg_size_pretty(pg_total_relation_size(relid)) AS total_size
FROM pg_catalog.pg_statio_user_tables
ORDER BY pg_total_relation_size(relid) DESC;
```

### Enable Extensions
```sql
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";
CREATE EXTENSION IF NOT EXISTS "btree_gin";
```

---

## Redis Commands

### Connect
```bash
redis-cli
```

### Useful Commands
```bash
# List all keys
KEYS *

# Check key type
TYPE key_name

# Get value
GET key_name

# Delete key
DEL key_name

# Clear all
FLUSHDB

# Monitor commands in real-time
MONITOR

# Check memory usage
INFO memory
```

### Laravel Cache Commands
```bash
php artisan cache:table    # Create cache table migration
php artisan cache:clear    # Clear application cache
```

---

## Git Workflow

### Feature Branch
```bash
git checkout -b feature/module-name
git add .
git commit -m "feat: add reservation creation"
git push origin feature/module-name
```

### Commit Message Convention
```
feat: Add new feature
fix: Fix bug
docs: Update documentation
style: Code style changes (no logic)
refactor: Code refactoring
test: Add/update tests
chore: Maintenance tasks
```

### Before Pushing
```bash
git pull origin main
composer lint
composer analyse
composer test
git push origin feature/branch-name
```

---

## Common Issues & Solutions

### Issue: Permission Denied
```bash
# Linux/Mac
sudo chown -R $USER:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Windows (Laragon)
# Run as Administrator
```

### Issue: Class Not Found
```bash
composer dump-autoload
composer dump-autoload --optimize
```

### Issue: Migration Errors
```bash
php artisan migrate:status          # Check migration status
php artisan migrate:rollback        # Rollback last batch
php artisan migrate:reset           # Reset all migrations
php artisan migrate:refresh         # Rollback & re-run
```

### Issue: N+1 Query Problem
```php
// Bad - N+1 queries
$reservations = Reservation::all();
foreach ($reservations as $r) {
    echo $r->guestProfile->first_name;
}

// Good - Eager loading
$reservations = Reservation::with('guestProfile')->get();
```

### Issue: Memory Limit
```bash
# Increase memory limit temporarily
php -d memory_limit=512M artisan test

# Or in php.ini
memory_limit = 512M
```

---

## Environment Variables Quick Reference

```env
# Application
APP_NAME="Hotel PMS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database (PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pms_test
DB_USERNAME=pms_user
DB_PASSWORD=secret

# Cache & Queue (Redis)
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Mail (Development - log all)
MAIL_MAILER=log

# Debugging
RAY_ENABLED=true
IGNITION_ENABLED=true
```

---

## Testing Examples

### Unit Test
```php
#[Test]
public function it_calculates_total_correctly(): void
{
    $reservation = Reservation::factory()->create([
        'total_amount' => 500,
        'paid_amount' => 200,
    ]);
    
    expect($reservation->balance())->toBe(300);
}
```

### Feature Test
```php
#[Test]
public function user_can_create_reservation(): void
{
    $user = createUserWithRole('front_desk');
    
    $response = $this->actingAs($user)
        ->post(route('reservations.store'), [
            'room_id' => 1,
            'guest_profile_id' => 1,
            'check_in_date' => now()->addDays(7),
            'check_out_date' => now()->addDays(10),
        ]);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('reservations', ['adults' => 2]);
}
```

### API Test
```php
#[Test]
public function api_returns_reservations(): void
{
    $token = getApiToken(User::factory()->create());
    
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/v1/reservations');
    
    $response->assertOk()
        ->assertJsonStructure(['data' => ['*' => ['id', 'reference']]]);
}
```

---

## Useful Packages

### Development
```bash
composer require --dev barryvdh/laravel-debugbar    # Debug toolbar
composer require --dev spatie/laravel-ray           # Debugging
composer require --dev barryvdh/laravel-ide-helper   # IDE helpers
composer require --dev beyondcode/laravel-dump-server # Dump server
```

### Production
```bash
composer require spatie/laravel-backup      # Database backup
composer require spatie/laravel-activitylog # Activity logging
composer require sentry/sentry-laravel       # Error tracking
```

---

## Performance Tips

1. **Use eager loading** to prevent N+1 queries
2. **Cache frequently accessed data** with cache tags
3. **Queue long-running tasks** (emails, OTA sync)
4. **Use database indexes** for common queries
5. **Enable query logging in production** to find slow queries
6. **Use `select()` instead of `get()`** when you don't need all columns
7. **Chunk large datasets** instead of loading all at once

```php
// Good practices
Reservation::with(['guest', 'room'])->get();
Cache::tags(['hotel-1'])->remember('key', 3600, fn() => $data);
SyncToOta::dispatch($reservation);
Reservation::select(['id', 'status'])->get();
Reservation::chunk(200, fn($reservations) => /* process */);
```

---

## Resources

- [Laravel Docs](https://laravel.com/docs)
- [Vue 3 Docs](https://vuejs.org/guide)
- [Inertia Docs](https://inertiajs.com/)
- [Pest Docs](https://pestphp.com/docs)
- [PostgreSQL Docs](https://www.postgresql.org/docs/)

---

**Keep this reference handy! 📌**
