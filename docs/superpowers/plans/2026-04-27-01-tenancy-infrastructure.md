# Tenancy Infrastructure Implementation Plan

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
            $table->string('name');                         // "Starter", "Pro", "Enterprise"
            $table->string('slug')->unique();
            $table->unsignedInteger('property_limit')->default(1);
            $table->unsignedInteger('room_limit')->default(50);
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_annual', 10, 2)->default(0);
            $table->boolean('trial_enabled')->default(false);
            $table->unsignedInteger('trial_days')->default(14);
            $table->json('modules_included')->nullable();   // ["pms","housekeeping"]
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
            $table->json('dependencies')->nullable();       // ["pos"] = requires POS add-on
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
# Move all non-landlord migration files
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

- [ ] **Step 4.2: Update multitenancy config to point to tenant migration path**

In `config/multitenancy.php`, add/update the `migration_path` under `actions`:

The Spatie `MigrateTenantAction` uses a custom path — override it by publishing and editing, or by creating a custom action. Add to `config/multitenancy.php`:

```php
// Add this key at the top level of the config array:
'migration_paths' => [
    database_path('migrations/tenant'),
],
```

Then create `app/Tenancy/Actions/MigrateTenantAction.php`:

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

Update `config/multitenancy.php` actions:
```php
'actions' => [
    'make_tenant_current_action'     => MakeTenantCurrentAction::class,
    'forget_tenant_action'           => ForgetCurrentTenantAction::class,
    'make_queue_tenant_aware_action' => MakeQueueTenantAwareAction::class,
    'migrate_tenant'                 => \App\Tenancy\Actions\MigrateTenantAction::class,
],
```

- [ ] **Step 4.3: Verify the move didn't break existing test suite**

```bash
php artisan test
```

Expected: all tests that were passing before still pass. (Tests use SQLite in-memory — they will need to reference the new path. Update `phpunit.xml` if needed.)

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

return [
    'tenant_finder' => TenantFinder::class,

    'tenant_artisan_search_fields' => ['id', 'domain'],

    'switch_tenant_tasks' => [
        SwitchTenantDatabaseTask::class,
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

        // Admin domain does not resolve to any tenant
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
- Create: `tests/Feature/Tenancy/TenantMiddlewareTest.php`

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

- [ ] **Step 7.2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/Tenancy/TenantMiddlewareTest.php
```

Expected: FAIL.

- [ ] **Step 7.3: Create NeedsTenant middleware**

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

- [ ] **Step 7.4: Create SuperAdminOnly middleware**

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
        $adminDomain = config('app.admin_domain', 'admin.pms.test');

        if ($request->getHost() !== $adminDomain) {
            abort(404);
        }

        return $next($request);
    }
}
```

- [ ] **Step 7.5: Create EnsureSubscriptionActive middleware**

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

- [ ] **Step 7.6: Create EnsurePropertyOnboarded middleware**

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
        // Skip the check if we're already on the onboarding route
        if ($request->routeIs('onboarding.*')) {
            return $next($request);
        }

        $propertyCount = DB::table('properties')->count();

        if ($propertyCount === 0) {
            return redirect()->route('onboarding.property.create');
        }

        return $next($request);
    }
}
```

- [ ] **Step 7.7: Register middleware aliases in bootstrap/app.php**

In `bootstrap/app.php`, inside `->withMiddleware(function (Middleware $middleware) {`, add:

```php
$middleware->alias([
    'needs.tenant'              => \App\Http\Middleware\NeedsTenant::class,
    'super.admin.only'          => \App\Http\Middleware\SuperAdminOnly::class,
    'ensure.subscription.active'=> \App\Http\Middleware\EnsureSubscriptionActive::class,
    'ensure.property.onboarded' => \App\Http\Middleware\EnsurePropertyOnboarded::class,
    // Keep existing ones:
    'auth.token'                => \App\Http\Middleware\AuthTokenMiddleware::class,
]);
```

Also add Spatie's middleware group to automatically resolve tenant on every request:

```php
$middleware->web(append: [
    \Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession::class,
]);
```

- [ ] **Step 7.8: Run the tests**

```bash
php artisan test tests/Feature/Tenancy/TenantMiddlewareTest.php
```

Expected: 3 tests PASS.

- [ ] **Step 7.9: Commit**

```bash
git add app/Http/Middleware/NeedsTenant.php \
        app/Http/Middleware/SuperAdminOnly.php \
        app/Http/Middleware/EnsureSubscriptionActive.php \
        app/Http/Middleware/EnsurePropertyOnboarded.php \
        bootstrap/app.php \
        tests/Feature/Tenancy/TenantMiddlewareTest.php
git commit -m "feat: add NeedsTenant, SuperAdminOnly, EnsureSubscriptionActive, EnsurePropertyOnboarded middleware"
```

---

## Task 8: Route Groups

**Files:**
- Modify: `routes/web.php`
- Create: `routes/super-admin.php`
- Create: `routes/tenant.php`

- [ ] **Step 8.1: Restructure routes/web.php**

Replace the full `routes/web.php` with:

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin Routes  (admin.pms.test)
|--------------------------------------------------------------------------
*/
Route::domain(config('app.admin_domain', 'admin.pms.test'))
    ->middleware(['super.admin.only'])
    ->group(base_path('routes/super-admin.php'));

/*
|--------------------------------------------------------------------------
| Tenant PMS Routes  ({tenant}.pms.test)
|--------------------------------------------------------------------------
*/
Route::middleware([
    'needs.tenant',
    'ensure.subscription.active',
])
->group(base_path('routes/tenant.php'));
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

    // Tenant routes — will be expanded in Phase 1
    Route::prefix('tenants')->name('super-admin.tenants.')->group(function (): void {
        Route::get('/', fn () => Inertia::render('SuperAdmin/Tenants/Index'))->name('index');
    });
});
```

- [ ] **Step 8.3: Create routes/tenant.php (migrate existing routes here)**

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

// Onboarding wizard (must come BEFORE the auth.token + ensure.property.onboarded group)
Route::middleware(['auth.token'])->prefix('onboarding')->name('onboarding.')->group(function (): void {
    Route::get('/property/create', fn () => Inertia::render('Onboarding/Property/Create'))
        ->name('property.create');
});

// All protected PMS routes
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

- [ ] **Step 8.4: Run all tests to ensure nothing is broken**

```bash
php artisan test
```

Expected: all tests pass. Fix any route name conflicts.

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
- Create: `tests/Feature/Tenancy/TenantProvisioningTest.php`

- [ ] **Step 9.1: Write failing tests**

Create `tests/Feature/Tenancy/TenantProvisioningTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

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

- [ ] **Step 9.2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/Tenancy/TenantProvisioningTest.php
```

Expected: FAIL — command not found.

- [ ] **Step 9.3: Create TenantCreate command**

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
    protected $signature   = 'tenant:create {name : The hotel name} {domain : The subdomain (e.g. marriott.pms.test)}';
    protected $description = 'Provision a new tenant with an isolated database';

    public function handle(): int
    {
        $name   = $this->argument('name');
        $domain = $this->argument('domain');
        $slug   = Str::slug($name);
        $dbName = 'pms_' . Str::replace('-', '_', $slug);

        // Guard: duplicate domain
        if (Tenant::on('landlord')->where('domain', $domain)->exists()) {
            $this->error("A tenant with domain [{$domain}] already exists.");
            return self::FAILURE;
        }

        // Create tenant record
        $tenant = Tenant::create([
            'name'     => $name,
            'slug'     => $slug,
            'domain'   => $domain,
            'database' => $dbName,
            'status'   => 'active',
        ]);

        $this->info("Tenant record created (ID: {$tenant->id})");

        // Create the database
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $this->info("Database [{$dbName}] created");

        // Run tenant migrations
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

- [ ] **Step 9.4: Create TenantMigrate command**

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

- [ ] **Step 9.5: Register commands in bootstrap/app.php**

In `bootstrap/app.php`, inside `->withCommands([`:
```php
\App\Console\Commands\TenantCreate::class,
\App\Console\Commands\TenantMigrate::class,
```

- [ ] **Step 9.6: Run tests**

```bash
php artisan test tests/Feature/Tenancy/TenantProvisioningTest.php
```

Expected: 2 tests PASS.

- [ ] **Step 9.7: Commit**

```bash
git add app/Console/Commands/TenantCreate.php \
        app/Console/Commands/TenantMigrate.php \
        tests/Feature/Tenancy/TenantProvisioningTest.php \
        bootstrap/app.php
git commit -m "feat: add tenant:create and tenant:migrate artisan commands"
```

---

## Task 10: Integration Smoke Test

End-to-end verification that a full tenant request cycle works correctly.

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

    // Fake tenant DB switching (use SQLite in test env)
    $this->withServerVariables(['HTTP_HOST' => 'smoke-test.pms.test'])
         ->get('/login')
         ->assertStatus(200);

    // Verify the correct tenant was resolved
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

    // Simulate authenticated user with no properties
    $this->withServerVariables(['HTTP_HOST' => 'new.pms.test'])
         ->actingAs(/* user */ \App\Models\User::factory()->create())
         ->get('/dashboard')
         ->assertRedirectToRoute('onboarding.property.create');
});
```

- [ ] **Step 10.2: Run the full integration test**

```bash
php artisan test tests/Feature/Tenancy/TenantResolutionTest.php -v
```

Expected: 4 tests PASS.

- [ ] **Step 10.3: Run the entire test suite**

```bash
composer run test
```

Expected: all tests pass, no regressions.

- [ ] **Step 10.4: Final commit**

```bash
git add tests/Feature/Tenancy/TenantResolutionTest.php
git commit -m "test: add integration tests for full tenant resolution cycle"
```

---

## Phase 0 Completion Checklist

Run these checks before marking Phase 0 done and starting Phase 1:

- [ ] `php artisan db:show --database=landlord` → shows landlord DB tables (tenants, subscription_plans, subscriptions, invoices, add_ons, plan_add_ons)
- [ ] `php artisan tenant:create "Test Hotel" test.pms.local` → creates record + DB + runs migrations
- [ ] `php artisan tenant:migrate test.pms.local` → runs any pending migrations on that tenant
- [ ] A request to an unknown domain returns 404
- [ ] A request to a suspended tenant's domain returns 403
- [ ] A request to an active tenant with 0 properties redirects to `/onboarding/property/create`
- [ ] `composer run test` → all tests PASS
- [ ] `./vendor/bin/pint` → no code style violations
