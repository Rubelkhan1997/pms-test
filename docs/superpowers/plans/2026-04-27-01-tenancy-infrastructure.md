# Tenancy Infrastructure Implementation Plan — v2

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Wire `spatie/laravel-multitenancy` so that each request to a tenant domain automatically switches to that tenant's isolated MySQL database, with no cross-tenant data leakage possible by design.

**Architecture:** DB-per-tenant via `SwitchTenantDatabaseTask`. A custom `TenantFinder` reads the request hostname and resolves the tenant from the landlord DB. Four middleware guards (NeedsTenant, SuperAdminOnly, EnsureSubscriptionActive, EnsurePropertyOnboarded) form a layered access control chain. Provisioning happens via Artisan commands.

**Tech Stack:** Laravel 13, `spatie/laravel-multitenancy` (already installed), MySQL 8, Pest PHP.

---

## File Map

| Action | File | Responsibility |
|---|---|---|
| Modify | `config/database.php` | Add `landlord` DB connection |
| Modify | `config/multitenancy.php` | Wire finder, model, tasks, paths |
| Modify | `.env.example` | Add landlord DB env vars |
| Create | `app/Models/Tenant.php` | Custom Tenant model with billing fields |
| Create | `database/migrations/landlord/2026_04_27_000001_expand_tenants_table.php` | Add status, plan_id, trial_ends_at etc. |
| Create | `database/migrations/landlord/2026_04_27_000002_create_subscription_plans_table.php` | |
| Create | `database/migrations/landlord/2026_04_27_000003_create_subscriptions_table.php` | |
| Create | `database/migrations/landlord/2026_04_27_000004_create_invoices_landlord_table.php` | |
| Create | `database/migrations/landlord/2026_04_27_000005_create_add_ons_table.php` | |
| Create | `database/migrations/landlord/2026_04_27_000006_create_plan_add_ons_table.php` | |
| Move | `database/migrations/*.php` → `database/migrations/tenant/` | Isolate tenant migrations |
| Create | `app/Tenancy/TenantFinder.php` | Domain → Tenant lookup |
| Create | `app/Http/Middleware/NeedsTenant.php` | Resolve tenant or 404 |
| Create | `app/Http/Middleware/SuperAdminOnly.php` | Guard landlord domain |
| Create | `app/Http/Middleware/EnsureSubscriptionActive.php` | Guard suspended tenants |
| Create | `app/Http/Middleware/EnsurePropertyOnboarded.php` | Redirect to wizard |
| Modify | `bootstrap/app.php` | Register middleware aliases |
| Modify | `routes/web.php` | Split into admin + tenant route groups |
| Create | `routes/super-admin.php` | Super Admin page routes |
| Create | `routes/tenant.php` | Tenant PMS page routes |
| Create | `routes/tenant-api.php` | Tenant API routes (replace routes/api.php) |
| Create | `app/Console/Commands/TenantCreate.php` | Provision new tenant |
| Create | `app/Console/Commands/TenantMigrate.php` | Run migrations on one tenant |
| Create | `database/factories/TenantFactory.php` | Test factory |
| Create | `tests/Feature/Tenancy/TenantResolutionTest.php` | Domain routing tests |
| Create | `tests/Feature/Tenancy/TenantMiddlewareTest.php` | Middleware chain tests |
| Create | `tests/Feature/Tenancy/TenantProvisioningTest.php` | Command tests |
| **NEW** | `app/Tenancy/Tasks/PrefixCacheTask.php` | Per-tenant cache prefix |
| **NEW** | `app/Listeners/TenantEventSubscriber.php` | Tenant lifecycle event handlers |
| **NEW** | `app/Console/Commands/TenantEachCommand.php` | Run command across all tenants |
| **NEW** | `tests/TestCase.php` | Fix DB transactions for landlord + tenant |
| **NEW** | `app/Tenancy/LandlordExecutor.php` | Helper for cross-context execution |

---

## Task 1: Landlord Database Connection

**Files:**
- Modify: `config/database.php`
- Modify: `.env.example`

- [ ] **Step 1.1: Add landlord connection to database config**

In `config/database.php`, add inside the `'connections'` array after the `mysql` entry:

```php
'landlord' => [
    'driver'         => 'mysql',
    'url'            => env('LANDLORD_DB_URL'),
    'host'           => env('LANDLORD_DB_HOST', '127.0.0.1'),
    'port'           => env('LANDLORD_DB_PORT', '3306'),
    'database'       => env('LANDLORD_DB_DATABASE', 'platform_db'),
    'username'       => env('LANDLORD_DB_USERNAME', 'root'),
    'password'       => env('LANDLORD_DB_PASSWORD', ''),
    'unix_socket'    => env('LANDLORD_DB_SOCKET', ''),
    'charset'        => env('DB_CHARSET', 'utf8mb4'),
    'collation'      => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
    'prefix'         => '',
    'prefix_indexes' => true,
    'strict'         => true,
    'engine'         => null,
    'options'        => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
    ]) : [],
],
```

- [ ] **Step 1.2: Add landlord env vars to `.env.example`**

After the existing `DB_*` block in `.env.example`, add:

```dotenv
LANDLORD_DB_HOST=127.0.0.1
LANDLORD_DB_PORT=3306
LANDLORD_DB_DATABASE=platform_db
LANDLORD_DB_USERNAME=root
LANDLORD_DB_PASSWORD=
```

Also add to your local `.env` file with real values.

- [ ] **Step 1.3: Create the landlord database manually**

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS platform_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

- [ ] **Step 1.4: Verify landlord connection works**

```bash
php artisan db:show --database=landlord
```

Expected: shows table count (0) and connection details without errors.

---

## Task 2: Custom Tenant Model

**Files:**
- Create: `app/Models/Tenant.php`
- Create: `database/migrations/landlord/2026_04_27_000001_expand_tenants_table.php`
- Create: `database/factories/TenantFactory.php`

- [ ] **Step 2.1: Write the failing test**

Create `tests/Feature/Tenancy/TenantModelTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Tenant;

it('creates a tenant with required fields', function (): void {
    $tenant = Tenant::create([
        'name'     => 'Grand Hotel',
        'slug'     => 'grand-hotel',
        'domain'   => 'grand.pms.test',
        'database' => 'pms_grand_hotel',
        'status'   => 'active',
    ]);

    expect($tenant->id)->toBeInt()
        ->and($tenant->name)->toBe('Grand Hotel')
        ->and($tenant->status)->toBe('active')
        ->and($tenant->isActive())->toBeTrue();
});

it('detects suspended tenant correctly', function (): void {
    $tenant = Tenant::factory()->create(['status' => 'suspended']);

    expect($tenant->isActive())->toBeFalse()
        ->and($tenant->isSuspended())->toBeTrue();
});

it('detects trial tenant correctly', function (): void {
    $tenant = Tenant::factory()->create([
        'status'        => 'trial',
        'trial_ends_at' => now()->addDays(7),
    ]);

    expect($tenant->isOnTrial())->toBeTrue()
        ->and($tenant->isTrialExpired())->toBeFalse();
});
```

- [ ] **Step 2.2: Run test to verify it fails**

```bash
php artisan test tests/Feature/Tenancy/TenantModelTest.php
```

Expected: FAIL — `App\Models\Tenant` not found.

- [ ] **Step 2.3: Expand landlord tenants table migration**

Create `database/migrations/landlord/2026_04_27_000001_expand_tenants_table.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('landlord')->table('tenants', function (Blueprint $table): void {
            $table->string('slug')->unique()->after('name');
            $table->enum('status', ['pending', 'active', 'trial', 'suspended', 'cancelled'])
                  ->default('pending')->after('database');
            $table->timestamp('trial_ends_at')->nullable()->after('status');
            $table->foreignId('plan_id')->nullable()->constrained('subscription_plans')->after('trial_ends_at');
            $table->string('contact_name')->nullable()->after('plan_id');
            $table->string('contact_email')->nullable()->after('contact_name');
            $table->string('contact_phone')->nullable()->after('contact_email');
            $table->timestamp('email_verified_at')->nullable()->after('contact_phone');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->table('tenants', function (Blueprint $table): void {
            $table->dropColumn([
                'slug', 'status', 'trial_ends_at', 'plan_id',
                'contact_name', 'contact_email', 'contact_phone',
                'email_verified_at', 'deleted_at',
            ]);
        });
    }
};
```

- [ ] **Step 2.4: Create the custom Tenant model**

Create `app/Models/Tenant.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'landlord';

    protected $fillable = [
        'name', 'slug', 'domain', 'database',
        'status', 'trial_ends_at', 'plan_id',
        'contact_name', 'contact_email', 'contact_phone',
        'email_verified_at',
    ];

    protected $casts = [
        'trial_ends_at'      => 'datetime',
        'email_verified_at'  => 'datetime',
    ];

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial'], true);
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isFuture();
    }

    public function isTrialExpired(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isPast();
    }

    public function getDatabaseName(): string
    {
        return $this->database;
    }
}
```

- [ ] **Step 2.5: Create Tenant factory**

Create `database/factories/TenantFactory.php`:

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = $this->faker->company();
        $slug = Str::slug($name);

        return [
            'name'     => $name,
            'slug'     => $slug,
            'domain'   => $slug . '.pms.test',
            'database' => 'pms_' . Str::replace('-', '_', $slug),
            'status'   => 'active',
        ];
    }

    public function suspended(): static
    {
        return $this->state(['status' => 'suspended']);
    }

    public function trial(int $days = 14): static
    {
        return $this->state([
            'status'        => 'trial',
            'trial_ends_at' => now()->addDays($days),
        ]);
    }

    public function expiredTrial(): static
    {
        return $this->state([
            'status'        => 'trial',
            'trial_ends_at' => now()->subDay(),
        ]);
    }
}
```

- [ ] **Step 2.6: Run landlord migrations**

```bash
php artisan migrate --path=database/migrations/landlord --database=landlord
```

Expected: runs both the original tenants migration and the new expand migration.

- [ ] **Step 2.7: Run the test to verify it passes**

```bash
php artisan test tests/Feature/Tenancy/TenantModelTest.php
```

Expected: 3 tests PASS.

- [ ] **Step 2.8: Commit**

```bash
git add app/Models/Tenant.php database/factories/TenantFactory.php \
        database/migrations/landlord/2026_04_27_000001_expand_tenants_table.php \
        tests/Feature/Tenancy/TenantModelTest.php \
        config/database.php .env.example
git commit -m "feat: add custom Tenant model with billing status fields and landlord DB connection"
```

---

## Task 3: Landlord Billing Migrations

**Files:**
- Create: `database/migrations/landlord/2026_04_27_000002_create_subscription_plans_table.php`
- Create: `database/migrations/landlord/2026_04_27_000003_create_subscriptions_table.php`
- Create: `database/migrations/landlord/2026_04_27_000004_create_invoices_landlord_table.php`
- Create: `database/migrations/landlord/2026_04_27_000005_create_add_ons_table.php`
- Create: `database/migrations/landlord/2026_04_27_000006_create_plan_add_ons_table.php`

- [ ] **Step 3.1: Create subscription_plans migration**

```php
<?php
// database/migrations/landlord/2026_04_27_000002_create_subscription_plans_table.php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('landlord')->create('subscription_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('property_limit')->default(1);
            $table->unsignedInteger('room_limit')->default(50);
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_annual', 10, 2)->default(0);
            $table->boolean('trial_enabled')->default(false);
            $table->unsignedInteger('trial_days')->default(14);
            $table->json('modules_included')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('subscription_plans');
    }
};
```

- [ ] **Step 3.2: Create subscriptions migration**

```php
<?php
// database/migrations/landlord/2026_04_27_000003_create_subscriptions_table.php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('landlord')->create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans');
            $table->enum('billing_cycle', ['monthly', 'annual'])->default('monthly');
            $table->enum('status', ['trial', 'active', 'past_due', 'cancelled'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->date('current_period_start')->nullable();
            $table->date('current_period_end')->nullable();
            $table->unsignedInteger('property_count')->default(0);
            $table->unsignedInteger('room_count')->default(0);
            $table->json('add_ons')->nullable();
            $table->date('next_invoice_date')->nullable();
            $table->timestamps();
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('subscriptions');
    }
};
```

- [ ] **Step 3.3: Create invoices (landlord) migration**

```php
<?php
// database/migrations/landlord/2026_04_27_000004_create_invoices_landlord_table.php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('landlord')->create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('subscriptions');
            $table->decimal('amount', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->char('currency', 3)->default('USD');
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue'])->default('draft');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('invoices');
    }
};
```

- [ ] **Step 3.4: Create add_ons and plan_add_ons migrations**

```php
<?php
// database/migrations/landlord/2026_04_27_000005_create_add_ons_table.php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('landlord')->create('add_ons', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('pricing_type', ['fixed', 'per_property', 'per_room'])->default('fixed');
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('billing_cycle', ['inherit', 'monthly', 'annual'])->default('inherit');
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('requires_activation')->default(false);
            $table->json('dependencies')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('landlord')->create('plan_add_ons', function (Blueprint $table): void {
            $table->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->foreignId('add_on_id')->constrained('add_ons')->cascadeOnDelete();
            $table->primary(['plan_id', 'add_on_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('plan_add_ons');
        Schema::connection('landlord')->dropIfExists('add_ons');
    }
};
```

- [ ] **Step 3.5: Run all landlord migrations**

```bash
php artisan migrate --path=database/migrations/landlord --database=landlord
```

Expected: 5 new tables created, no errors.

- [ ] **Step 3.6: Commit**

```bash
git add database/migrations/landlord/
git commit -m "feat: add landlord billing schema (subscription_plans, subscriptions, invoices, add_ons)"
```

---

## Task 4: Reorganize Tenant Migrations

All existing migrations in `database/migrations/` (except the `landlord/` subfolder) must move to `database/migrations/tenant/`. These run per-tenant when provisioning a new hotel DB.

- [ ] **Step 4.1: Create tenant migrations directory and move files**

```bash
mkdir -p database/migrations/tenant
mv database/migrations/0001_01_01_000000_create_users_table.php database/migrations/tenant/
mv database/migrations/0001_01_01_000001_create_cache_table.php database/migrations/tenant/
mv database/migrations/0001_01_01_000002_create_jobs_table.php database/migrations/tenant/
mv database/migrations/2026_03_05_100453_create_permission_tables.php database/migrations/tenant/
mv database/migrations/2026_03_05_100453_create_personal_access_tokens_table.php database/migrations/tenant/
mv database/migrations/2026_03_05_120000_create_hotels_table.php database/migrations/tenant/
mv database/migrations/2026_03_05_120100_create_rooms_table.php database/migrations/tenant/
mv database/migrations/2026_03_05_120300_create_ota_syncs_table.php database/migrations/tenant/
mv database/migrations/2026_03_05_120400_create_agents_and_guest_profiles_table.php database/migrations/tenant/
mv database/migrations/2026_03_05_120450_create_reservations_table.php database/migrations/tenant/
mv database/migrations/2026_03_05_120500_create_housekeeping_and_maintenance_tables.php database/migrations/tenant/
mv database/migrations/2026_03_05_120600_create_pos_tables.php database/migrations/tenant/
mv database/migrations/2026_03_05_120700_create_report_snapshots_table.php database/migrations/tenant/
mv database/migrations/2026_03_05_120800_create_mobile_tasks_table.php database/migrations/tenant/
mv database/migrations/2026_03_05_120900_create_hr_tables.php database/migrations/tenant/
mv database/migrations/2026_03_29_120704_add_role_to_users_table.php database/migrations/tenant/
mv database/migrations/2026_04_02_041901_create_currencies_table.php database/migrations/tenant/
```

- [ ] **Step 4.2: Create custom MigrateTenantAction**

Create `app/Tenancy/Actions/MigrateTenantAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tenancy\Actions;

use Illuminate\Support\Facades\Artisan;
use Spatie\Multitenancy\Actions\MigrateTenantAction as BaseMigrateTenantAction;
use Spatie\Multitenancy\Models\Tenant;

class MigrateTenantAction extends BaseMigrateTenantAction
{
    public function execute(Tenant $tenant): void
    {
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path'     => 'database/migrations/tenant',
            '--force'    => true,
        ]);
    }
}
```

- [ ] **Step 4.3: Verify the move didn't break existing test suite**

```bash
php artisan test
```

Expected: all tests that were passing before still pass.

- [ ] **Step 4.4: Commit**

```bash
git add database/migrations/tenant/ app/Tenancy/Actions/ config/multitenancy.php
git commit -m "refactor: move all tenant migrations to database/migrations/tenant/"
```

---

## Task 5: Wire Multitenancy Config

**Files:**
- Modify: `config/multitenancy.php`

- [ ] **Step 5.1: Update config/multitenancy.php**

Replace the full `config/multitenancy.php` content:

```php
<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Tenancy\TenantFinder;
use App\Tenancy\Actions\MigrateTenantAction;
use App\Tenancy\Tasks\PrefixCacheTask;
use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Queue\CallQueuedClosure;
use Spatie\Multitenancy\Jobs\NotTenantAware;
use Spatie\Multitenancy\Jobs\TenantAware;
use Spatie\Multitenancy\Actions\ForgetCurrentTenantAction;
use Spatie\Multitenancy\Actions\MakeQueueTenantAwareAction;
use Spatie\Multitenancy\Actions\MakeTenantCurrentAction;
use Spatie\Multitenancy\Tasks\SwitchTenantDatabaseTask;
use Spatie\Multitenancy\Tasks\SwitchRouteCacheTask;

return [
    'tenant_finder' => TenantFinder::class,

    'tenant_artisan_search_fields' => ['id', 'domain'],

    'switch_tenant_tasks' => [
        SwitchTenantDatabaseTask::class,
        PrefixCacheTask::class,        // ← per-tenant cache isolation
        SwitchRouteCacheTask::class,   // ← per-tenant route cache
    ],

    'tenant_model' => Tenant::class,

    'queues_are_tenant_aware_by_default' => true,

    'tenant_database_connection_name' => 'mysql',

    'landlord_database_connection_name' => 'landlord',

    'current_tenant_context_key' => 'tenantId',

    'current_tenant_container_key' => 'currentTenant',

    'shared_routes_cache' => false,

    'actions' => [
        'make_tenant_current_action'     => MakeTenantCurrentAction::class,
        'forget_current_tenant_action'   => ForgetCurrentTenantAction::class,
        'make_queue_tenant_aware_action' => MakeQueueTenantAwareAction::class,
        'migrate_tenant'                 => MigrateTenantAction::class,
    ],

    'queueable_to_job' => [
        SendQueuedMailable::class      => 'mailable',
        SendQueuedNotifications::class => 'notification',
        CallQueuedClosure::class       => 'closure',
        CallQueuedListener::class      => 'class',
        BroadcastEvent::class          => 'event',
    ],

    'tenant_aware_interface'     => TenantAware::class,
    'not_tenant_aware_interface' => NotTenantAware::class,
    'tenant_aware_jobs'          => [],
    'not_tenant_aware_jobs'      => [],
];
```

- [ ] **Step 5.2: Commit**

```bash
git add config/multitenancy.php
git commit -m "feat: wire multitenancy config with domain finder and SwitchTenantDatabaseTask"
```

---

## Task 6: Domain-Based Tenant Finder

**Files:**
- Create: `app/Tenancy/TenantFinder.php`
- Create: `tests/Feature/Tenancy/TenantFinderTest.php`

- [ ] **Step 6.1: Write the failing test**

Create `tests/Feature/Tenancy/TenantFinderTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Tenancy\TenantFinder;
use Illuminate\Http\Request;

it('finds tenant by exact domain match', function (): void {
    $tenant = Tenant::factory()->create(['domain' => 'grand.pms.test']);

    $request = Request::create('http://grand.pms.test/dashboard');
    $finder  = new TenantFinder();

    $found = $finder->findForRequest($request);

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($tenant->id);
});

it('returns null for unknown domain', function (): void {
    $request = Request::create('http://unknown.pms.test/dashboard');
    $finder  = new TenantFinder();

    $found = $finder->findForRequest($request);

    expect($found)->toBeNull();
});

it('returns null for landlord admin domain', function (): void {
    $request = Request::create('http://admin.pms.test/dashboard');
    $finder  = new TenantFinder();

    $found = $finder->findForRequest($request);

    expect($found)->toBeNull();
});
```

- [ ] **Step 6.2: Run test to verify it fails**

```bash
php artisan test tests/Feature/Tenancy/TenantFinderTest.php
```

Expected: FAIL — `App\Tenancy\TenantFinder` not found.

- [ ] **Step 6.3: Create TenantFinder**

Create `app/Tenancy/TenantFinder.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Spatie\Multitenancy\TenantFinder\TenantFinder as BaseTenantFinder;

class TenantFinder extends BaseTenantFinder
{
    public function findForRequest(Request $request): ?Tenant
    {
        $host = $request->getHost();

        if ($host === config('app.admin_domain', 'admin.pms.test')) {
            return null;
        }

        return Tenant::on('landlord')
            ->where('domain', $host)
            ->where('status', '!=', 'cancelled')
            ->first();
    }
}
```

- [ ] **Step 6.4: Add admin domain to config**

In `config/app.php`, add:
```php
'admin_domain' => env('ADMIN_DOMAIN', 'admin.pms.test'),
```

Add to `.env.example`:
```dotenv
ADMIN_DOMAIN=admin.pms.test
```

- [ ] **Step 6.5: Run test to verify it passes**

```bash
php artisan test tests/Feature/Tenancy/TenantFinderTest.php
```

Expected: 3 tests PASS.

- [ ] **Step 6.6: Commit**

```bash
git add app/Tenancy/TenantFinder.php tests/Feature/Tenancy/TenantFinderTest.php config/app.php .env.example
git commit -m "feat: add domain-based TenantFinder that excludes admin domain"
```

---

## Task 7: Middleware Chain

**Files:**
- Create: `app/Http/Middleware/NeedsTenant.php`
- Create: `app/Http/Middleware/SuperAdminOnly.php`
- Create: `app/Http/Middleware/EnsureSubscriptionActive.php`
- Create: `app/Http/Middleware/EnsurePropertyOnboarded.php`
- Modify: `bootstrap/app.php`

- [ ] **Step 7.1: Write failing tests**

Create `tests/Feature/Tenancy/TenantMiddlewareTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Tenant;

it('returns 404 for unknown domain', function (): void {
    $this->withServerVariables(['HTTP_HOST' => 'unknown.pms.test'])
         ->get('/dashboard')
         ->assertStatus(404);
});

it('allows request for known active tenant', function (): void {
    Tenant::factory()->create(['domain' => 'hotel-a.pms.test', 'status' => 'active']);

    $this->withServerVariables(['HTTP_HOST' => 'hotel-a.pms.test'])
         ->get('/')
         ->assertRedirectToRoute('dashboard');
});

it('returns 403 for suspended tenant', function (): void {
    Tenant::factory()->suspended()->create(['domain' => 'suspended.pms.test']);

    $this->withServerVariables(['HTTP_HOST' => 'suspended.pms.test'])
         ->get('/dashboard')
         ->assertStatus(403);
});
```

- [ ] **Step 7.2: Create NeedsTenant middleware**

Create `app/Http/Middleware/NeedsTenant.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Models\Concerns\UsesTenantModel;
use Symfony\Component\HttpFoundation\Response;

class NeedsTenant
{
    use UsesTenantModel;

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->getTenantModel()::checkCurrent()) {
            abort(404, 'Tenant not found.');
        }

        return $next($request);
    }
}
```

- [ ] **Step 7.3: Create SuperAdminOnly middleware**

Create `app/Http/Middleware/SuperAdminOnly.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->getHost() !== config('app.admin_domain', 'admin.pms.test')) {
            abort(404);
        }

        return $next($request);
    }
}
```

- [ ] **Step 7.4: Create EnsureSubscriptionActive middleware**

Create `app/Http/Middleware/EnsureSubscriptionActive.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Models\Concerns\UsesTenantModel;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionActive
{
    use UsesTenantModel;

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->getTenantModel()::current();

        if ($tenant && $tenant->isSuspended()) {
            abort(403, 'Your account is suspended. Please contact support.');
        }

        return $next($request);
    }
}
```

- [ ] **Step 7.5: Create EnsurePropertyOnboarded middleware**

Create `app/Http/Middleware/EnsurePropertyOnboarded.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsurePropertyOnboarded
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('onboarding.*')) {
            return $next($request);
        }

        if (DB::table('properties')->count() === 0) {
            return redirect()->route('onboarding.property.create');
        }

        return $next($request);
    }
}
```

- [ ] **Step 7.6: Register middleware aliases in bootstrap/app.php**

```php
$middleware->alias([
    'needs.tenant'               => \App\Http\Middleware\NeedsTenant::class,
    'super.admin.only'           => \App\Http\Middleware\SuperAdminOnly::class,
    'ensure.subscription.active' => \App\Http\Middleware\EnsureSubscriptionActive::class,
    'ensure.property.onboarded'  => \App\Http\Middleware\EnsurePropertyOnboarded::class,
    'auth.token'                 => \App\Http\Middleware\AuthTokenMiddleware::class,
]);

$middleware->web(append: [
    \Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession::class,
]);
```

- [ ] **Step 7.7: Run tests**

```bash
php artisan test tests/Feature/Tenancy/TenantMiddlewareTest.php
```

Expected: 3 tests PASS.

- [ ] **Step 7.8: Commit**

```bash
git add app/Http/Middleware/ bootstrap/app.php tests/Feature/Tenancy/TenantMiddlewareTest.php
git commit -m "feat: add NeedsTenant, SuperAdminOnly, EnsureSubscriptionActive, EnsurePropertyOnboarded middleware"
```

---

## Task 8: Route Groups

**Files:**
- Modify: `routes/web.php`
- Create: `routes/super-admin.php`
- Create: `routes/tenant.php`

- [ ] **Step 8.1: Restructure routes/web.php**

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::domain(config('app.admin_domain', 'admin.pms.test'))
    ->middleware(['super.admin.only'])
    ->group(base_path('routes/super-admin.php'));

Route::middleware([
    'needs.tenant',
    'ensure.subscription.active',
])->group(base_path('routes/tenant.php'));
```

- [ ] **Step 8.2: Create routes/super-admin.php**

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => redirect()->route('super-admin.dashboard'))->name('super-admin.home');

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::get('/dashboard', fn () => Inertia::render('SuperAdmin/Dashboard/Index'))
        ->name('super-admin.dashboard');

    Route::prefix('tenants')->name('super-admin.tenants.')->group(function (): void {
        Route::get('/', fn () => Inertia::render('SuperAdmin/Tenants/Index'))->name('index');
    });
});
```

- [ ] **Step 8.3: Create routes/tenant.php**

```php
<?php

declare(strict_types=1);

use App\Modules\Auth\Controllers\Web\AuthController;
use App\Modules\FrontDesk\Controllers\Web\HotelController;
use App\Modules\FrontDesk\Controllers\Web\RoomController;
use App\Modules\FrontDesk\Controllers\Web\ReservationController as FrontDeskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => redirect()->route('dashboard'))->name('home');

Route::middleware(['guest'])->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
});

Route::middleware(['auth.token'])->prefix('onboarding')->name('onboarding.')->group(function (): void {
    Route::get('/property/create', fn () => Inertia::render('Onboarding/Property/Create'))
        ->name('property.create');
});

Route::middleware(['auth.token', 'ensure.property.onboarded'])->group(function (): void {
    Route::get('/dashboard', fn () => Inertia::render('Dashboard/Index'))->name('dashboard');

    Route::prefix('hotels')->name('hotels.')->group(function (): void {
        Route::get('/', [HotelController::class, 'index'])->name('index')->middleware('permission:view hotels');
        Route::get('/create', [HotelController::class, 'create'])->name('create')->middleware('permission:create hotels');
        Route::get('/{hotel}', [HotelController::class, 'show'])->name('show')->middleware('permission:view hotels');
        Route::get('/{hotel}/edit', [HotelController::class, 'edit'])->name('edit')->middleware('permission:edit hotels');
    });

    Route::prefix('rooms')->name('rooms.')->group(function (): void {
        Route::get('/', [RoomController::class, 'index'])->name('index')->middleware('permission:view rooms');
        Route::get('/create', [RoomController::class, 'create'])->name('create')->middleware('permission:create rooms');
        Route::get('/{room}', [RoomController::class, 'show'])->name('show')->middleware('permission:view rooms');
        Route::get('/{room}/edit', [RoomController::class, 'edit'])->name('edit')->middleware('permission:edit rooms');
    });

    Route::prefix('reservations')->name('reservations.')->group(function (): void {
        Route::get('/', [FrontDeskController::class, 'index'])->name('index')->middleware('permission:view reservations');
        Route::get('/create', [FrontDeskController::class, 'create'])->name('create')->middleware('permission:create reservations');
        Route::get('/{reservation}', [FrontDeskController::class, 'show'])->name('show')->middleware('permission:view reservations');
        Route::get('/{reservation}/edit', [FrontDeskController::class, 'edit'])->name('edit')->middleware('permission:edit reservations');
    });
});
```

- [ ] **Step 8.4: Run all tests**

```bash
php artisan test
```

- [ ] **Step 8.5: Commit**

```bash
git add routes/web.php routes/super-admin.php routes/tenant.php
git commit -m "feat: split routes into domain-based tenant and super-admin groups"
```

---

## Task 9: Tenant Provisioning Command

**Files:**
- Create: `app/Console/Commands/TenantCreate.php`
- Create: `app/Console/Commands/TenantMigrate.php`

- [ ] **Step 9.1: Write failing tests**

Create `tests/Feature/Tenancy/TenantProvisioningTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Tenant;

it('creates tenant record via artisan command', function (): void {
    $this->artisan('tenant:create', [
        'name'   => 'Test Hotel',
        'domain' => 'test-hotel.pms.test',
    ])->assertSuccessful();

    expect(
        Tenant::on('landlord')->where('domain', 'test-hotel.pms.test')->exists()
    )->toBeTrue();
});

it('prevents duplicate domain creation', function (): void {
    Tenant::factory()->create(['domain' => 'existing.pms.test']);

    $this->artisan('tenant:create', [
        'name'   => 'Another Hotel',
        'domain' => 'existing.pms.test',
    ])->assertFailed();
});
```

- [ ] **Step 9.2: Create TenantCreate command**

Create `app/Console/Commands/TenantCreate.php`:

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantCreate extends Command
{
    protected $signature   = 'tenant:create {name : The hotel name} {domain : The subdomain}';
    protected $description = 'Provision a new tenant with an isolated database';

    public function handle(): int
    {
        $name   = $this->argument('name');
        $domain = $this->argument('domain');
        $slug   = Str::slug($name);
        $dbName = 'pms_' . Str::replace('-', '_', $slug);

        if (Tenant::on('landlord')->where('domain', $domain)->exists()) {
            $this->error("A tenant with domain [{$domain}] already exists.");
            return self::FAILURE;
        }

        $tenant = Tenant::create([
            'name'     => $name,
            'slug'     => $slug,
            'domain'   => $domain,
            'database' => $dbName,
            'status'   => 'active',
        ]);

        $this->info("Tenant record created (ID: {$tenant->id})");

        DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $this->info("Database [{$dbName}] created");

        $tenant->makeCurrent();
        $this->call('migrate', [
            '--database' => 'mysql',
            '--path'     => 'database/migrations/tenant',
            '--force'    => true,
        ]);
        Tenant::forgetCurrent();

        $this->info("Tenant [{$name}] provisioned successfully.");
        $this->table(['Field', 'Value'], [
            ['Domain',   $domain],
            ['Database', $dbName],
            ['Status',   'active'],
        ]);

        return self::SUCCESS;
    }
}
```

- [ ] **Step 9.3: Create TenantMigrate command**

Create `app/Console/Commands/TenantMigrate.php`:

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class TenantMigrate extends Command
{
    protected $signature   = 'tenant:migrate {domain : The tenant domain}';
    protected $description = 'Run pending migrations on a single tenant database';

    public function handle(): int
    {
        $tenant = Tenant::on('landlord')->where('domain', $this->argument('domain'))->first();

        if (! $tenant) {
            $this->error("Tenant [{$this->argument('domain')}] not found.");
            return self::FAILURE;
        }

        $tenant->makeCurrent();
        $this->call('migrate', [
            '--database' => 'mysql',
            '--path'     => 'database/migrations/tenant',
            '--force'    => true,
        ]);
        Tenant::forgetCurrent();

        $this->info("Migrations run for [{$tenant->name}].");
        return self::SUCCESS;
    }
}
```

- [ ] **Step 9.4: Register commands in bootstrap/app.php**

```php
\App\Console\Commands\TenantCreate::class,
\App\Console\Commands\TenantMigrate::class,
```

- [ ] **Step 9.5: Run tests**

```bash
php artisan test tests/Feature/Tenancy/TenantProvisioningTest.php
```

Expected: 2 tests PASS.

- [ ] **Step 9.6: Commit**

```bash
git add app/Console/Commands/TenantCreate.php app/Console/Commands/TenantMigrate.php \
        tests/Feature/Tenancy/TenantProvisioningTest.php bootstrap/app.php
git commit -m "feat: add tenant:create and tenant:migrate artisan commands"
```

---

## Task 10: Integration Smoke Test

- [ ] **Step 10.1: Write integration test**

Create `tests/Feature/Tenancy/TenantResolutionTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Tenant;

it('resolves correct tenant for domain and switches database', function (): void {
    $tenant = Tenant::factory()->create([
        'domain'   => 'smoke-test.pms.test',
        'status'   => 'active',
        'database' => 'pms_smoke_test',
    ]);

    $this->withServerVariables(['HTTP_HOST' => 'smoke-test.pms.test'])
         ->get('/login')
         ->assertStatus(200);

    expect(app('currentTenant'))->not->toBeNull()
        ->and(app('currentTenant')->id)->toBe($tenant->id);
});

it('returns 404 when no tenant matches the domain', function (): void {
    $this->withServerVariables(['HTTP_HOST' => 'nope.pms.test'])
         ->get('/login')
         ->assertStatus(404);
});

it('returns 403 when tenant is suspended', function (): void {
    Tenant::factory()->suspended()->create(['domain' => 'suspended.pms.test']);

    $this->withServerVariables(['HTTP_HOST' => 'suspended.pms.test'])
         ->get('/dashboard')
         ->assertStatus(403);
});

it('redirects to onboarding when tenant has no properties', function (): void {
    Tenant::factory()->create(['domain' => 'new.pms.test', 'status' => 'active']);

    $this->withServerVariables(['HTTP_HOST' => 'new.pms.test'])
         ->actingAs(\App\Models\User::factory()->create())
         ->get('/dashboard')
         ->assertRedirectToRoute('onboarding.property.create');
});
```

- [ ] **Step 10.2: Run**

```bash
php artisan test tests/Feature/Tenancy/TenantResolutionTest.php -v
```

Expected: 4 tests PASS.

- [ ] **Step 10.3: Commit**

```bash
git add tests/Feature/Tenancy/TenantResolutionTest.php
git commit -m "test: add integration tests for full tenant resolution cycle"
```

---

## ════════════════════════════════════════════════
## NEW TASKS — Spatie Full Feature Coverage
## ════════════════════════════════════════════════

---

## Task 11: Per-Tenant Cache Isolation (PrefixCacheTask)

**Why:** Without a per-tenant cache prefix, tenant A-র cached data tenant B read করতে পারে। এটা data leakage।

**Files:**
- Create: `app/Tenancy/Tasks/PrefixCacheTask.php`
- Create: `tests/Feature/Tenancy/CacheIsolationTest.php`

- [ ] **Step 11.1: Write failing test**

Create `tests/Feature/Tenancy/CacheIsolationTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

it('uses different cache prefixes for different tenants', function (): void {
    $tenantA = Tenant::factory()->create(['domain' => 'hotel-a.pms.test']);
    $tenantB = Tenant::factory()->create(['domain' => 'hotel-b.pms.test']);

    $tenantA->makeCurrent();
    Cache::put('room_count', 50, 60);
    $prefixA = config('cache.prefix');

    $tenantB->makeCurrent();
    Cache::put('room_count', 99, 60);
    $prefixB = config('cache.prefix');

    expect($prefixA)->not->toBe($prefixB);

    // Tenant A-র data tenant B-তে পাওয়া যাবে না
    $tenantA->makeCurrent();
    expect(Cache::get('room_count'))->toBe(50);

    Tenant::forgetCurrent();
});

it('restores default cache prefix when tenant is forgotten', function (): void {
    $originalPrefix = config('cache.prefix');

    $tenant = Tenant::factory()->create();
    $tenant->makeCurrent();

    expect(config('cache.prefix'))->not->toBe($originalPrefix);

    Tenant::forgetCurrent();

    expect(config('cache.prefix'))->toBe($originalPrefix);
});
```

- [ ] **Step 11.2: Run to verify it fails**

```bash
php artisan test tests/Feature/Tenancy/CacheIsolationTest.php
```

Expected: FAIL.

- [ ] **Step 11.3: Create PrefixCacheTask**

Create `app/Tenancy/Tasks/PrefixCacheTask.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tenancy\Tasks;

use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class PrefixCacheTask implements SwitchTenantTask
{
    private string $originalPrefix;

    public function __construct()
    {
        $this->originalPrefix = config('cache.prefix');
    }

    public function makeCurrent(IsTenant $tenant): void
    {
        $this->setCachePrefix('tenant_' . $tenant->id);
    }

    public function forgetCurrent(): void
    {
        $this->setCachePrefix($this->originalPrefix);
    }

    private function setCachePrefix(string $prefix): void
    {
        config(['cache.prefix' => $prefix]);

        // Cache manager-এর store গুলো flush করা দরকার যাতে নতুন prefix নেয়
        app('cache')->forgetDriver(config('cache.default'));
    }
}
```

- [ ] **Step 11.4: Verify PrefixCacheTask is in switch_tenant_tasks**

`config/multitenancy.php`-এ নিশ্চিত করো:

```php
'switch_tenant_tasks' => [
    SwitchTenantDatabaseTask::class,
    PrefixCacheTask::class,       // ← এটা আছে
    SwitchRouteCacheTask::class,
],
```

- [ ] **Step 11.5: Run tests**

```bash
php artisan test tests/Feature/Tenancy/CacheIsolationTest.php
```

Expected: 2 tests PASS.

- [ ] **Step 11.6: Commit**

```bash
git add app/Tenancy/Tasks/PrefixCacheTask.php tests/Feature/Tenancy/CacheIsolationTest.php
git commit -m "feat: add PrefixCacheTask for per-tenant cache isolation"
```

---

## Task 12: Tenant Lifecycle Events

**Why:** Tenant switch হলে audit log, Sentry context, Telescope tagging, বা cache warm-up করার দরকার হয়। Spatie এই events fire করে — আমাদের শুধু listen করতে হবে।

**Spatie events:**
- `MakingTenantCurrentEvent` — tasks run হওয়ার আগে
- `MadeTenantCurrentEvent` — tasks run হওয়ার পরে, DB switched
- `ForgettingCurrentTenantEvent` — forget শুরু হওয়ার আগে
- `ForgotCurrentTenantEvent` — forget সম্পন্ন
- `TenantNotFoundForRequestEvent` — TenantFinder null দিলে

**Files:**
- Create: `app/Listeners/TenantEventSubscriber.php`
- Modify: `app/Providers/EventServiceProvider.php` (অথবা `bootstrap/app.php`)
- Create: `tests/Feature/Tenancy/TenantEventsTest.php`

- [ ] **Step 12.1: Write failing tests**

Create `tests/Feature/Tenancy/TenantEventsTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Tenant;
use Illuminate\Support\Facades\Event;
use Spatie\Multitenancy\Events\MadeTenantCurrentEvent;
use Spatie\Multitenancy\Events\ForgotCurrentTenantEvent;
use Spatie\Multitenancy\Events\TenantNotFoundForRequestEvent;

it('fires MadeTenantCurrentEvent when tenant is made current', function (): void {
    Event::fake([MadeTenantCurrentEvent::class]);

    $tenant = Tenant::factory()->create();
    $tenant->makeCurrent();

    Event::assertDispatched(MadeTenantCurrentEvent::class, function ($event) use ($tenant) {
        return $event->tenant->id === $tenant->id;
    });
});

it('fires ForgotCurrentTenantEvent when tenant is forgotten', function (): void {
    Event::fake([ForgotCurrentTenantEvent::class]);

    $tenant = Tenant::factory()->create();
    $tenant->makeCurrent();
    Tenant::forgetCurrent();

    Event::assertDispatched(ForgotCurrentTenantEvent::class);
});

it('fires TenantNotFoundForRequestEvent for unknown domain', function (): void {
    Event::fake([TenantNotFoundForRequestEvent::class]);

    $this->withServerVariables(['HTTP_HOST' => 'ghost.pms.test'])
         ->get('/dashboard');

    Event::assertDispatched(TenantNotFoundForRequestEvent::class);
});
```

- [ ] **Step 12.2: Run to verify it fails**

```bash
php artisan test tests/Feature/Tenancy/TenantEventsTest.php
```

- [ ] **Step 12.3: Create TenantEventSubscriber**

Create `app/Listeners/TenantEventSubscriber.php`:

```php
<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Events\ForgotCurrentTenantEvent;
use Spatie\Multitenancy\Events\ForgettingCurrentTenantEvent;
use Spatie\Multitenancy\Events\MadeTenantCurrentEvent;
use Spatie\Multitenancy\Events\MakingTenantCurrentEvent;
use Spatie\Multitenancy\Events\TenantNotFoundForRequestEvent;

class TenantEventSubscriber
{
    /**
     * Tenant set হওয়ার আগে — Sentry/Telescope context set করার জায়গা
     */
    public function onMakingTenantCurrent(MakingTenantCurrentEvent $event): void
    {
        // উদাহরণ: \Sentry\configureScope(fn($scope) => $scope->setTag('tenant', $event->tenant->slug));
    }

    /**
     * Tenant সম্পূর্ণভাবে current — DB switched, এখন কাজ করা নিরাপদ
     */
    public function onMadeTenantCurrent(MadeTenantCurrentEvent $event): void
    {
        Log::withContext(['tenant_id' => $event->tenant->id, 'tenant' => $event->tenant->slug]);
    }

    /**
     * Tenant forget শুরু হওয়ার আগে
     */
    public function onForgettingCurrentTenant(ForgettingCurrentTenantEvent $event): void
    {
        // cleanup শুরুর আগে যা করার
    }

    /**
     * Tenant সম্পূর্ণভাবে forgotten — context clear করো
     */
    public function onForgotCurrentTenant(ForgotCurrentTenantEvent $event): void
    {
        Log::withContext([]);
    }

    /**
     * Request-এ কোনো tenant পাওয়া যায়নি — log করো, alert পাঠাও
     */
    public function onTenantNotFound(TenantNotFoundForRequestEvent $event): void
    {
        Log::warning('Tenant not found for request', [
            'host' => $event->request->getHost(),
            'url'  => $event->request->fullUrl(),
            'ip'   => $event->request->ip(),
        ]);
    }

    public function subscribe(): array
    {
        return [
            MakingTenantCurrentEvent::class    => 'onMakingTenantCurrent',
            MadeTenantCurrentEvent::class      => 'onMadeTenantCurrent',
            ForgettingCurrentTenantEvent::class => 'onForgettingCurrentTenant',
            ForgotCurrentTenantEvent::class     => 'onForgotCurrentTenant',
            TenantNotFoundForRequestEvent::class => 'onTenantNotFound',
        ];
    }
}
```

- [ ] **Step 12.4: Register the subscriber**

`bootstrap/app.php`-এ `->withEvents()` block-এ অথবা `AppServiceProvider`-এ:

```php
// bootstrap/app.php
->withEvents(function (Dispatcher $events) {
    $events->subscribe(\App\Listeners\TenantEventSubscriber::class);
})
```

অথবা `AppServiceProvider::boot()`:

```php
\Illuminate\Support\Facades\Event::subscribe(
    \App\Listeners\TenantEventSubscriber::class
);
```

- [ ] **Step 12.5: Run tests**

```bash
php artisan test tests/Feature/Tenancy/TenantEventsTest.php
```

Expected: 3 tests PASS.

- [ ] **Step 12.6: Commit**

```bash
git add app/Listeners/TenantEventSubscriber.php tests/Feature/Tenancy/TenantEventsTest.php
git commit -m "feat: add TenantEventSubscriber for lifecycle logging and context management"
```

---

## Task 13: Loop Over All Tenants — `eachCurrent()`

**Why:** Scheduled jobs, bulk migration, report generation, এবং broadcast notifications-এর জন্য সব tenant-এ iterate করতে হয়। Spatie-এর `eachCurrent()` method প্রতিটি tenant-কে current করে callback run করে।

**Files:**
- Create: `app/Console/Commands/TenantEachCommand.php`
- Modify: `routes/console.php` (scheduler example)
- Create: `tests/Feature/Tenancy/TenantLoopTest.php`

- [ ] **Step 13.1: Write failing tests**

Create `tests/Feature/Tenancy/TenantLoopTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

it('eachCurrent runs callback for every active tenant', function (): void {
    $tenantA = Tenant::factory()->create(['domain' => 'loop-a.pms.test']);
    $tenantB = Tenant::factory()->create(['domain' => 'loop-b.pms.test']);
    Tenant::factory()->create(['domain' => 'loop-c.pms.test', 'status' => 'cancelled']);

    $visited = [];

    Tenant::all()->eachCurrent(function (Tenant $tenant) use (&$visited): void {
        $visited[] = $tenant->id;
    });

    // Cancelled tenant loop-এ আসবে না যদি filter করি — এখানে all() তাই সব আসে
    expect($visited)->toContain($tenantA->id)
        ->and($visited)->toContain($tenantB->id);
});

it('restores null current tenant after eachCurrent loop', function (): void {
    Tenant::factory()->count(2)->create();

    Tenant::all()->eachCurrent(function (Tenant $tenant): void {
        // কিছু করো
    });

    expect(Tenant::current())->toBeNull();
});

it('tenant:each artisan command runs given command for all tenants', function (): void {
    Tenant::factory()->count(3)->create();

    $this->artisan('tenant:each', ['artisan_command' => 'cache:clear'])
         ->assertSuccessful();
});
```

- [ ] **Step 13.2: Run to verify they fail**

```bash
php artisan test tests/Feature/Tenancy/TenantLoopTest.php
```

- [ ] **Step 13.3: Create TenantEachCommand**

Create `app/Console/Commands/TenantEachCommand.php`:

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class TenantEachCommand extends Command
{
    protected $signature   = 'tenant:each {artisan_command : The artisan command to run for each tenant}';
    protected $description = 'Run an artisan command for each tenant';

    public function handle(): int
    {
        $command = $this->argument('artisan_command');
        $tenants = Tenant::on('landlord')
            ->whereNotIn('status', ['cancelled', 'suspended'])
            ->get();

        if ($tenants->isEmpty()) {
            $this->warn('No active tenants found.');
            return self::SUCCESS;
        }

        $this->info("Running [{$command}] for {$tenants->count()} tenant(s)...");

        $tenants->eachCurrent(function (Tenant $tenant) use ($command): void {
            $this->line("  → [{$tenant->name}] ({$tenant->domain})");
            $this->call($command);
        });

        $this->info('Done.');
        return self::SUCCESS;
    }
}
```

- [ ] **Step 13.4: Scheduler example — `routes/console.php`**

Add to `routes/console.php`:

```php
use App\Models\Tenant;
use Illuminate\Support\Facades\Schedule;

// প্রতি tenant-এর জন্য daily report
Schedule::call(function (): void {
    Tenant::on('landlord')
        ->where('status', 'active')
        ->get()
        ->eachCurrent(function (Tenant $tenant): void {
            dispatch(new \App\Jobs\GenerateDailyReport($tenant));
        });
})->dailyAt('02:00')->name('daily-reports-all-tenants');

// সব tenant-এ cache clear করো প্রতি রাতে
Schedule::command('tenant:each cache:clear')->dailyAt('03:00');
```

- [ ] **Step 13.5: Register TenantEachCommand in bootstrap/app.php**

```php
\App\Console\Commands\TenantEachCommand::class,
```

- [ ] **Step 13.6: Run tests**

```bash
php artisan test tests/Feature/Tenancy/TenantLoopTest.php
```

Expected: 3 tests PASS.

- [ ] **Step 13.7: Commit**

```bash
git add app/Console/Commands/TenantEachCommand.php \
        tests/Feature/Tenancy/TenantLoopTest.php \
        routes/console.php
git commit -m "feat: add TenantEachCommand and scheduler examples using eachCurrent()"
```

---

## Task 14: Cross-Context Execution — `Tenant::execute()` & `Landlord::execute()`

**Why:** Super admin dashboard থেকে নির্দিষ্ট tenant-এর DB-তে কাজ করতে হলে, অথবা tenant request-এর ভিতর থেকে landlord data লিখতে হলে এই pattern দরকার।

**Files:**
- Create: `app/Tenancy/LandlordExecutor.php`
- Create: `tests/Feature/Tenancy/CrossContextExecutionTest.php`

- [ ] **Step 14.1: Write failing tests**

Create `tests/Feature/Tenancy/CrossContextExecutionTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Landlord;

it('execute() switches to tenant context and back', function (): void {
    $tenantA = Tenant::factory()->create(['domain' => 'exec-a.pms.test']);
    $tenantB = Tenant::factory()->create(['domain' => 'exec-b.pms.test']);

    // Start from tenantA context
    $tenantA->makeCurrent();

    // Execute something in tenantB context
    $result = $tenantB->execute(function (Tenant $tenant): string {
        return $tenant->domain;
    });

    // After execute, tenantA should be current again
    expect($result)->toBe('exec-b.pms.test')
        ->and(Tenant::current()->id)->toBe($tenantA->id);
});

it('Landlord::execute() clears tenant context temporarily', function (): void {
    $tenant = Tenant::factory()->create();
    $tenant->makeCurrent();

    expect(Tenant::current())->not->toBeNull();

    $result = Landlord::execute(function (): bool {
        return Tenant::current() === null;
    });

    // Landlord context-এ tenant null ছিল
    expect($result)->toBeTrue();
    // Execute শেষে tenant আবার current
    expect(Tenant::current()->id)->toBe($tenant->id);
});

it('execute() works from no-tenant (landlord) context', function (): void {
    $tenant = Tenant::factory()->create(['domain' => 'exec-new.pms.test']);

    // No current tenant (landlord context)
    expect(Tenant::current())->toBeNull();

    $executed = false;
    $tenant->execute(function (Tenant $t) use (&$executed): void {
        $executed = true;
        expect(Tenant::current()->id)->toBe($t->id);
    });

    expect($executed)->toBeTrue();
    // After execute, back to no tenant
    expect(Tenant::current())->toBeNull();
});
```

- [ ] **Step 14.2: Create LandlordExecutor helper**

এটা Spatie-এর `Landlord::execute()` এর একটি thin wrapper যা super admin routes-এ directly use করা যায়।

Create `app/Tenancy/LandlordExecutor.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Models\Tenant;
use Closure;
use Spatie\Multitenancy\Landlord;

/**
 * Helper class for cross-context execution in super-admin routes.
 *
 * Usage examples:
 *
 *   // Super admin route: flush a specific tenant's cache
 *   LandlordExecutor::forTenant($tenant, fn() => cache()->flush());
 *
 *   // Tenant route: write to landlord (e.g. update billing status)
 *   LandlordExecutor::asLandlord(fn() => $tenant->update(['status' => 'suspended']));
 */
class LandlordExecutor
{
    /**
     * Landlord context থেকে একটি specific tenant-এর context-এ কাজ করো।
     */
    public static function forTenant(Tenant $tenant, Closure $callback): mixed
    {
        return $tenant->execute($callback);
    }

    /**
     * যেকোনো context থেকে landlord context-এ কাজ করো।
     */
    public static function asLandlord(Closure $callback): mixed
    {
        return Landlord::execute($callback);
    }

    /**
     * Tenant-এর cache flush করো landlord context থেকে।
     */
    public static function flushTenantCache(Tenant $tenant): void
    {
        $tenant->execute(fn () => cache()->flush());
    }

    /**
     * Tenant-এর scheduled callback তৈরি করো (scheduler use)।
     * Callback যতবারই call হোক, সঠিক tenant context-এ run হবে।
     */
    public static function callbackFor(Tenant $tenant, Closure $callback): Closure
    {
        return $tenant->callback($callback);
    }
}
```

- [ ] **Step 14.3: Super admin route example**

`routes/super-admin.php`-এ add করো:

```php
use App\Tenancy\LandlordExecutor;

// Super admin → tenant cache flush
Route::delete('/tenants/{tenant}/cache', function (Tenant $tenant) {
    LandlordExecutor::flushTenantCache($tenant);
    return response()->json(['success' => true]);
})->name('super-admin.tenants.flush-cache');

// Super admin → suspend tenant
Route::patch('/tenants/{tenant}/suspend', function (Tenant $tenant) {
    LandlordExecutor::asLandlord(fn () => $tenant->update(['status' => 'suspended']));
    return response()->json(['success' => true]);
})->name('super-admin.tenants.suspend');
```

- [ ] **Step 14.4: Run tests**

```bash
php artisan test tests/Feature/Tenancy/CrossContextExecutionTest.php
```

Expected: 3 tests PASS.

- [ ] **Step 14.5: Commit**

```bash
git add app/Tenancy/LandlordExecutor.php \
        tests/Feature/Tenancy/CrossContextExecutionTest.php \
        routes/super-admin.php
git commit -m "feat: add LandlordExecutor for cross-context tenant/landlord execution"
```

---

## Task 15: Fix Test Database Transactions

**Why:** Default Laravel TestCase শুধু একটি connection-এ transaction wrap করে। Multitenancy-তে landlord + tenant দুটো আলাদা connection — দুটোতেই transaction দরকার, না হলে test data rollback হয় না এবং tests একে অপরকে pollute করে।

**Files:**
- Modify: `tests/TestCase.php`
- Create: `tests/Concerns/UsesTenantDatabase.php`

- [ ] **Step 15.1: Update tests/TestCase.php**

`tests/TestCase.php` replace করো:

```php
<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Event;
use Spatie\Multitenancy\Concerns\UsesMultitenancyConfig;
use Spatie\Multitenancy\Events\MadeTenantCurrentEvent;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;
    use UsesMultitenancyConfig;

    /**
     * Laravel-কে বলো দুটো connection-এই transaction শুরু করতে।
     * Landlord connection-এ tenant records, tenant connection-এ tenant data।
     */
    protected function connectionsToTransact(): array
    {
        return [
            $this->landlordDatabaseConnectionName(),   // 'landlord'
            $this->tenantDatabaseConnectionName(),     // 'mysql'
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        /**
         * যখন কোনো tenant current হয়, সেই connection-এও
         * transaction শুরু করো যাতে test isolation বজায় থাকে।
         */
        Event::listen(MadeTenantCurrentEvent::class, function (): void {
            $this->beginDatabaseTransaction();
        });
    }
}
```

- [ ] **Step 15.2: Create UsesTenantDatabase concern (optional, reusable)**

Create `tests/Concerns/UsesTenantDatabase.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Tenant;
use Illuminate\Support\Facades\Event;
use Spatie\Multitenancy\Events\MadeTenantCurrentEvent;

/**
 * Use this trait in any test class that needs to work with a tenant DB.
 *
 * Usage:
 *   use Tests\Concerns\UsesTenantDatabase;
 *
 *   it('does something in tenant DB', function (): void {
 *       $this->setUpTenant('hotel-test.pms.test');
 *       // Now you're in tenant context
 *   });
 */
trait UsesTenantDatabase
{
    protected ?Tenant $currentTestTenant = null;

    /**
     * একটি tenant তৈরি করো এবং সেটাকে current করো।
     */
    protected function setUpTenant(string $domain = 'test.pms.test'): Tenant
    {
        $this->currentTestTenant = Tenant::factory()->create([
            'domain' => $domain,
            'status' => 'active',
        ]);

        $this->currentTestTenant->makeCurrent();

        return $this->currentTestTenant;
    }

    /**
     * Test শেষে tenant context clear করো।
     */
    protected function tearDownTenant(): void
    {
        Tenant::forgetCurrent();
        $this->currentTestTenant = null;
    }
}
```

- [ ] **Step 15.3: Verify test isolation works**

```bash
php artisan test --parallel
```

Parallel test-এ যদি landlord data leak না করে, setup সঠিক।

- [ ] **Step 15.4: Write a test to confirm isolation**

Add to `tests/Feature/Tenancy/TestIsolationTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Tenant;

it('tenant created in one test does not bleed into another', function (): void {
    Tenant::factory()->create(['domain' => 'isolation-check.pms.test']);

    expect(Tenant::on('landlord')->where('domain', 'isolation-check.pms.test')->count())->toBe(1);
});

it('does not see tenant from previous test due to transaction rollback', function (): void {
    // এই test-এ আগের test-এর data থাকা উচিত নয়
    expect(Tenant::on('landlord')->where('domain', 'isolation-check.pms.test')->count())->toBe(0);
});
```

- [ ] **Step 15.5: Run**

```bash
php artisan test tests/Feature/Tenancy/TestIsolationTest.php -v
```

Expected: দুটো test-ই PASS (কারণ transaction rollback হয়েছে)।

- [ ] **Step 15.6: Commit**

```bash
git add tests/TestCase.php tests/Concerns/UsesTenantDatabase.php \
        tests/Feature/Tenancy/TestIsolationTest.php
git commit -m "fix: wrap both landlord and tenant connections in DatabaseTransactions for test isolation"
```

---

## Phase 0 Completion Checklist

Run these checks before marking Phase 0 done and starting Phase 1:

- [ ] `php artisan db:show --database=landlord` → shows landlord DB tables (tenants, subscription_plans, subscriptions, invoices, add_ons, plan_add_ons)
- [ ] `php artisan tenant:create "Test Hotel" test.pms.local` → creates record + DB + runs migrations
- [ ] `php artisan tenant:migrate test.pms.local` → runs any pending migrations on that tenant
- [ ] `php artisan tenant:each cache:clear` → clears cache for all active tenants
- [ ] A request to an unknown domain returns 404 **AND** logs a warning
- [ ] A request to a suspended tenant's domain returns 403
- [ ] A request to an active tenant with 0 properties redirects to `/onboarding/property/create`
- [ ] Tenant A-র cached data Tenant B পড়তে পারে না (PrefixCacheTask)
- [ ] `Tenant::execute()` and `Landlord::execute()` correctly switch context
- [ ] `composer run test` → all tests PASS
- [ ] `php artisan test --parallel` → no test pollution between landlord records
- [ ] `./vendor/bin/pint` → no code style violations
