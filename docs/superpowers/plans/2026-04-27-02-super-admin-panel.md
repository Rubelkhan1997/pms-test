# Super Admin Panel Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the Super Admin panel at `admin.pms.test` for platform operators to manage tenants, subscription plans, billing, and global settings — completely isolated from the tenant PMS.

**Architecture:** A new `SuperAdmin` module under `app/Modules/SuperAdmin/`. All routes live in `routes/super-admin.php` behind the `SuperAdminOnly` middleware. The panel uses its own `SuperAdminLayout.vue`. All data reads from the **landlord** DB connection via the custom Tenant model. Tenant creation triggers the `tenant:create` provisioning command internally.

**Tech Stack:** Laravel 13, Inertia.js v2, Vue 3 + TypeScript, Pinia, Tailwind CSS v4, Pest PHP.

**Depends on:** Phase 0 (Tenancy Infrastructure) fully complete.

---

## File Map

| Action | File | Responsibility |
|---|---|---|
| Create | `app/Modules/SuperAdmin/Controllers/Web/DashboardController.php` | Platform stats page |
| Create | `app/Modules/SuperAdmin/Controllers/Web/TenantController.php` | Tenant list/create/edit pages |
| Create | `app/Modules/SuperAdmin/Controllers/Web/SubscriptionPlanController.php` | Plans list/create/edit pages |
| Create | `app/Modules/SuperAdmin/Controllers/Api/V1/TenantController.php` | Tenant CRUD API |
| Create | `app/Modules/SuperAdmin/Controllers/Api/V1/SubscriptionPlanController.php` | Plan CRUD API |
| Create | `app/Modules/SuperAdmin/Services/TenantService.php` | Tenant business logic |
| Create | `app/Modules/SuperAdmin/Services/SubscriptionPlanService.php` | Plan business logic |
| Create | `app/Modules/SuperAdmin/Actions/CreateTenantAction.php` | Provision tenant |
| Create | `app/Modules/SuperAdmin/Actions/SuspendTenantAction.php` | Suspend tenant |
| Create | `app/Modules/SuperAdmin/Data/TenantData.php` | Tenant DTO |
| Create | `app/Modules/SuperAdmin/Data/SubscriptionPlanData.php` | Plan DTO |
| Create | `app/Modules/SuperAdmin/Requests/StoreTenantRequest.php` | Tenant create validation |
| Create | `app/Modules/SuperAdmin/Requests/UpdateTenantRequest.php` | Tenant update validation |
| Create | `app/Modules/SuperAdmin/Requests/StoreSubscriptionPlanRequest.php` | Plan create validation |
| Create | `app/Modules/SuperAdmin/Resources/TenantResource.php` | Tenant API Resource |
| Create | `app/Modules/SuperAdmin/Resources/SubscriptionPlanResource.php` | Plan API Resource |
| Modify | `routes/super-admin.php` | Add all Super Admin routes |
| Create | `routes/super-admin-api.php` | Super Admin API routes |
| Create | `resources/js/Layouts/SuperAdminLayout.vue` | Super Admin shell layout |
| Create | `resources/js/Pages/SuperAdmin/Dashboard/Index.vue` | Platform dashboard |
| Create | `resources/js/Pages/SuperAdmin/Tenants/Index.vue` | Tenant list |
| Create | `resources/js/Pages/SuperAdmin/Tenants/Create.vue` | Create tenant |
| Create | `resources/js/Pages/SuperAdmin/Tenants/Show.vue` | Tenant profile |
| Create | `resources/js/Pages/SuperAdmin/SubscriptionPlans/Index.vue` | Plans list |
| Create | `resources/js/Pages/SuperAdmin/SubscriptionPlans/Create.vue` | Create plan |
| Create | `resources/js/Stores/SuperAdmin/tenantStore.ts` | Tenant state |
| Create | `resources/js/Stores/SuperAdmin/subscriptionPlanStore.ts` | Plan state |
| Create | `resources/js/Composables/SuperAdmin/useTenants.ts` | Tenant composable |
| Create | `resources/js/Composables/SuperAdmin/useSubscriptionPlans.ts` | Plan composable |
| Create | `resources/js/Types/SuperAdmin/tenant.ts` | Tenant TS types |
| Create | `resources/js/Types/SuperAdmin/subscriptionPlan.ts` | Plan TS types |
| Create | `resources/js/Utils/Mappers/tenant.ts` | Tenant API ↔ TS mapper |
| Create | `resources/js/Utils/Mappers/subscriptionPlan.ts` | Plan API ↔ TS mapper |
| Create | `tests/Feature/SuperAdmin/TenantManagementTest.php` | Tenant CRUD tests |
| Create | `tests/Feature/SuperAdmin/SubscriptionPlanTest.php` | Plan CRUD tests |

---

## Task 1: SuperAdmin Module Skeleton + Routes

**Files:**
- Create: module directory structure
- Modify: `routes/super-admin.php`
- Create: `routes/super-admin-api.php`

- [x] **Step 1.1: Create module directory structure**

```bash
mkdir -p app/Modules/SuperAdmin/Controllers/Web
mkdir -p app/Modules/SuperAdmin/Controllers/Api/V1
mkdir -p app/Modules/SuperAdmin/Services
mkdir -p app/Modules/SuperAdmin/Actions
mkdir -p app/Modules/SuperAdmin/Data
mkdir -p app/Modules/SuperAdmin/Requests
mkdir -p app/Modules/SuperAdmin/Resources
```

- [x] **Step 1.2: Update routes/super-admin.php with all web routes**

```php
<?php

declare(strict_types=1);

use App\Modules\SuperAdmin\Controllers\Web\DashboardController;
use App\Modules\SuperAdmin\Controllers\Web\TenantController;
use App\Modules\SuperAdmin\Controllers\Web\SubscriptionPlanController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('super-admin.dashboard'))->name('super-admin.home');

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('super-admin.dashboard');

    // Tenants
    Route::prefix('tenants')->name('super-admin.tenants.')->group(function (): void {
        Route::get('/',           [TenantController::class, 'index'])->name('index');
        Route::get('/create',     [TenantController::class, 'create'])->name('create');
        Route::get('/{id}',       [TenantController::class, 'show'])->name('show');
        Route::get('/{id}/edit',  [TenantController::class, 'edit'])->name('edit');
    });

    // Subscription Plans
    Route::prefix('plans')->name('super-admin.plans.')->group(function (): void {
        Route::get('/',          [SubscriptionPlanController::class, 'index'])->name('index');
        Route::get('/create',    [SubscriptionPlanController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [SubscriptionPlanController::class, 'edit'])->name('edit');
    });
});
```

- [x] **Step 1.3: Create routes/super-admin-api.php**

```php
<?php

declare(strict_types=1);

use App\Modules\SuperAdmin\Controllers\Api\V1\TenantController;
use App\Modules\SuperAdmin\Controllers\Api\V1\SubscriptionPlanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1/admin')->group(function (): void {
    // Tenants
    Route::get('tenants',                 [TenantController::class, 'index']);
    Route::post('tenants',                [TenantController::class, 'store']);
    Route::get('tenants/{id}',            [TenantController::class, 'show']);
    Route::put('tenants/{id}',            [TenantController::class, 'update']);
    Route::patch('tenants/{id}/suspend',  [TenantController::class, 'suspend']);
    Route::patch('tenants/{id}/activate', [TenantController::class, 'activate']);

    // Subscription Plans
    Route::get('plans',        [SubscriptionPlanController::class, 'index']);
    Route::post('plans',       [SubscriptionPlanController::class, 'store']);
    Route::get('plans/{id}',   [SubscriptionPlanController::class, 'show']);
    Route::put('plans/{id}',   [SubscriptionPlanController::class, 'update']);
    Route::delete('plans/{id}',[SubscriptionPlanController::class, 'destroy']);
});
```

- [x] **Step 1.4: Register the API routes file in bootstrap/app.php**

In the `->withRouting(...)` call, add:
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    then: function (): void {
        Route::domain(config('app.admin_domain', 'admin.pms.test'))
            ->middleware(['super.admin.only', 'api'])
            ->prefix('api')
            ->group(base_path('routes/super-admin-api.php'));
    },
    ...
)
```

- [ ] **Step 1.5: Commit**

```bash
git add app/Modules/SuperAdmin/ routes/super-admin.php routes/super-admin-api.php bootstrap/app.php
git commit -m "feat: scaffold SuperAdmin module directory and route groups"
```

---

## Task 2: Subscription Plan CRUD

**Files:**
- Create: `app/Modules/SuperAdmin/Controllers/Api/V1/SubscriptionPlanController.php`
- Create: `app/Modules/SuperAdmin/Services/SubscriptionPlanService.php`
- Create: `app/Modules/SuperAdmin/Actions/CreateSubscriptionPlanAction.php`
- Create: `app/Modules/SuperAdmin/Data/SubscriptionPlanData.php`
- Create: `app/Modules/SuperAdmin/Requests/StoreSubscriptionPlanRequest.php`
- Create: `app/Modules/SuperAdmin/Resources/SubscriptionPlanResource.php`
- Create: `tests/Feature/SuperAdmin/SubscriptionPlanTest.php`

- [x] **Step 2.1: Write failing tests**

Create `tests/Feature/SuperAdmin/SubscriptionPlanTest.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Multitenancy\Models\SubscriptionPlan; // we'll use landlord DB

uses(RefreshDatabase::class);

it('creates a subscription plan', function (): void {
    $payload = [
        'name'            => 'Starter',
        'slug'            => 'starter',
        'property_limit'  => 1,
        'room_limit'      => 30,
        'price_monthly'   => 49.99,
        'price_annual'    => 499.00,
        'trial_enabled'   => true,
        'trial_days'      => 14,
        'modules_included'=> ['pms', 'housekeeping'],
    ];

    $this->withServerVariables(['HTTP_HOST' => config('app.admin_domain')])
         ->postJson('/api/v1/admin/plans', $payload)
         ->assertCreated()
         ->assertJsonPath('status', 1)
         ->assertJsonPath('data.name', 'Starter');
});

it('lists all active subscription plans', function (): void {
    \App\Models\SubscriptionPlan::factory()->count(3)->create();

    $this->withServerVariables(['HTTP_HOST' => config('app.admin_domain')])
         ->getJson('/api/v1/admin/plans')
         ->assertOk()
         ->assertJsonPath('status', 1)
         ->assertJsonCount(3, 'data.items');
});

it('rejects plan creation with missing name', function (): void {
    $this->withServerVariables(['HTTP_HOST' => config('app.admin_domain')])
         ->postJson('/api/v1/admin/plans', ['price_monthly' => 49.99])
         ->assertUnprocessable()
         ->assertJsonValidationErrors(['name']);
});
```

- [x] **Step 2.2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/SuperAdmin/SubscriptionPlanTest.php
```

Expected: FAIL.

- [x] **Step 2.3: Create SubscriptionPlan model**

Create `app/Models/SubscriptionPlan.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $connection = 'landlord';

    protected $fillable = [
        'name', 'slug', 'property_limit', 'room_limit',
        'price_monthly', 'price_annual',
        'trial_enabled', 'trial_days',
        'modules_included', 'is_active',
    ];

    protected $casts = [
        'modules_included' => 'array',
        'trial_enabled'    => 'boolean',
        'is_active'        => 'boolean',
        'price_monthly'    => 'decimal:2',
        'price_annual'     => 'decimal:2',
    ];
}
```

- [x] **Step 2.4: Create SubscriptionPlanData DTO**

Create `app/Modules/SuperAdmin/Data/SubscriptionPlanData.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Data;

use Spatie\LaravelData\Data;

class SubscriptionPlanData extends Data
{
    public function __construct(
        public readonly string  $name,
        public readonly string  $slug,
        public readonly int     $property_limit,
        public readonly int     $room_limit,
        public readonly float   $price_monthly,
        public readonly float   $price_annual,
        public readonly bool    $trial_enabled,
        public readonly int     $trial_days,
        public readonly ?array  $modules_included,
        public readonly bool    $is_active = true,
    ) {}
}
```

- [x] **Step 2.5: Create StoreSubscriptionPlanRequest**

Create `app/Modules/SuperAdmin/Requests/StoreSubscriptionPlanRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:100'],
            'slug'             => ['required', 'string', 'max:100', 'unique:landlord.subscription_plans,slug'],
            'property_limit'   => ['required', 'integer', 'min:1'],
            'room_limit'       => ['required', 'integer', 'min:1'],
            'price_monthly'    => ['required', 'numeric', 'min:0'],
            'price_annual'     => ['required', 'numeric', 'min:0'],
            'trial_enabled'    => ['boolean'],
            'trial_days'       => ['required_if:trial_enabled,true', 'integer', 'min:1', 'max:365'],
            'modules_included' => ['nullable', 'array'],
            'modules_included.*' => ['string'],
            'is_active'        => ['boolean'],
        ];
    }
}
```

- [x] **Step 2.6: Create SubscriptionPlanResource**

Create `app/Modules/SuperAdmin/Resources/SubscriptionPlanResource.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'slug'             => $this->slug,
            'property_limit'   => $this->property_limit,
            'room_limit'       => $this->room_limit,
            'price_monthly'    => $this->price_monthly,
            'price_annual'     => $this->price_annual,
            'trial_enabled'    => $this->trial_enabled,
            'trial_days'       => $this->trial_days,
            'modules_included' => $this->modules_included,
            'is_active'        => $this->is_active,
            'created_at'       => $this->created_at,
        ];
    }
}
```

- [x] **Step 2.7: Create CreateSubscriptionPlanAction**

Create `app/Modules/SuperAdmin/Actions/CreateSubscriptionPlanAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Actions;

use App\Models\SubscriptionPlan;
use App\Modules\SuperAdmin\Data\SubscriptionPlanData;

class CreateSubscriptionPlanAction
{
    public function execute(SubscriptionPlanData $data): SubscriptionPlan
    {
        return SubscriptionPlan::create($data->toArray());
    }
}
```

- [x] **Step 2.8: Create SubscriptionPlanService**

Create `app/Modules/SuperAdmin/Services/SubscriptionPlanService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Services;

use App\Models\SubscriptionPlan;
use App\Modules\SuperAdmin\Actions\CreateSubscriptionPlanAction;
use App\Modules\SuperAdmin\Data\SubscriptionPlanData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class SubscriptionPlanService
{
    public function __construct(
        private CreateSubscriptionPlanAction $createAction,
    ) {}

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return SubscriptionPlan::on('landlord')
            ->orderBy('price_monthly')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): SubscriptionPlan
    {
        return SubscriptionPlan::on('landlord')->findOrFail($id);
    }

    public function create(SubscriptionPlanData $data): SubscriptionPlan
    {
        return $this->createAction->execute($data);
    }

    public function update(SubscriptionPlan $plan, SubscriptionPlanData $data): SubscriptionPlan
    {
        $plan->update($data->toArray());
        return $plan->fresh();
    }

    public function delete(SubscriptionPlan $plan): void
    {
        $plan->delete();
    }
}
```

- [x] **Step 2.9: Create SubscriptionPlan API Controller**

Create `app/Modules/SuperAdmin/Controllers/Api/V1/SubscriptionPlanController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Data\SubscriptionPlanData;
use App\Modules\SuperAdmin\Requests\StoreSubscriptionPlanRequest;
use App\Modules\SuperAdmin\Resources\SubscriptionPlanResource;
use App\Modules\SuperAdmin\Services\SubscriptionPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function __construct(
        private readonly SubscriptionPlanService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $plans = $this->service->paginate((int) $request->get('per_page', 20));

        return response()->json([
            'status'  => 1,
            'data'    => [
                'items'      => SubscriptionPlanResource::collection($plans),
                'pagination' => [
                    'current_page' => $plans->currentPage(),
                    'per_page'     => $plans->perPage(),
                    'total'        => $plans->total(),
                    'last_page'    => $plans->lastPage(),
                ],
            ],
            'message' => 'Plans fetched successfully',
        ]);
    }

    public function store(StoreSubscriptionPlanRequest $request): JsonResponse
    {
        $plan = $this->service->create(SubscriptionPlanData::from($request->validated()));

        return response()->json([
            'status'  => 1,
            'data'    => new SubscriptionPlanResource($plan),
            'message' => 'Plan created successfully',
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $plan = $this->service->findOrFail($id);

        return response()->json([
            'status'  => 1,
            'data'    => new SubscriptionPlanResource($plan),
            'message' => 'Plan fetched successfully',
        ]);
    }

    public function update(StoreSubscriptionPlanRequest $request, int $id): JsonResponse
    {
        $plan    = $this->service->findOrFail($id);
        $updated = $this->service->update($plan, SubscriptionPlanData::from($request->validated()));

        return response()->json([
            'status'  => 1,
            'data'    => new SubscriptionPlanResource($updated),
            'message' => 'Plan updated successfully',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($this->service->findOrFail($id));

        return response()->json([
            'status'  => 1,
            'data'    => null,
            'message' => 'Plan deleted successfully',
        ]);
    }
}
```

- [ ] **Step 2.10: Run tests to verify they pass**

```bash
php artisan test tests/Feature/SuperAdmin/SubscriptionPlanTest.php
```

Expected: 3 tests PASS.

- [ ] **Step 2.11: Commit**

```bash
git add app/Modules/SuperAdmin/ app/Models/SubscriptionPlan.php \
        tests/Feature/SuperAdmin/SubscriptionPlanTest.php
git commit -m "feat: add SubscriptionPlan CRUD (model, service, action, controller, resource)"
```

---

## Task 3: Tenant Management API

**Files:**
- Create: `app/Modules/SuperAdmin/Controllers/Api/V1/TenantController.php`
- Create: `app/Modules/SuperAdmin/Services/TenantService.php`
- Create: `app/Modules/SuperAdmin/Actions/CreateTenantAction.php`
- Create: `app/Modules/SuperAdmin/Actions/SuspendTenantAction.php`
- Create: `app/Modules/SuperAdmin/Actions/ActivateTenantAction.php`
- Create: `app/Modules/SuperAdmin/Data/TenantData.php`
- Create: `app/Modules/SuperAdmin/Requests/StoreTenantRequest.php`
- Create: `app/Modules/SuperAdmin/Requests/UpdateTenantRequest.php`
- Create: `app/Modules/SuperAdmin/Resources/TenantResource.php`
- Create: `tests/Feature/SuperAdmin/TenantManagementTest.php`

- [x] **Step 3.1: Write failing tests**

Create `tests/Feature/SuperAdmin/TenantManagementTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Tenant;

it('lists tenants paginated', function (): void {
    Tenant::factory()->count(5)->create();

    $this->withServerVariables(['HTTP_HOST' => config('app.admin_domain')])
         ->getJson('/api/v1/admin/tenants')
         ->assertOk()
         ->assertJsonPath('status', 1)
         ->assertJsonCount(5, 'data.items');
});

it('creates a tenant via API and provisions database', function (): void {
    $this->withServerVariables(['HTTP_HOST' => config('app.admin_domain')])
         ->postJson('/api/v1/admin/tenants', [
             'name'          => 'Ocean View Hotel',
             'domain'        => 'ocean.pms.test',
             'contact_name'  => 'John Doe',
             'contact_email' => 'john@ocean.com',
             'status'        => 'active',
         ])
         ->assertCreated()
         ->assertJsonPath('status', 1)
         ->assertJsonPath('data.name', 'Ocean View Hotel');

    expect(Tenant::on('landlord')->where('domain', 'ocean.pms.test')->exists())->toBeTrue();
});

it('suspends a tenant', function (): void {
    $tenant = Tenant::factory()->create(['status' => 'active']);

    $this->withServerVariables(['HTTP_HOST' => config('app.admin_domain')])
         ->patchJson("/api/v1/admin/tenants/{$tenant->id}/suspend")
         ->assertOk()
         ->assertJsonPath('data.status', 'suspended');

    expect($tenant->fresh()->status)->toBe('suspended');
});

it('activates a suspended tenant', function (): void {
    $tenant = Tenant::factory()->suspended()->create();

    $this->withServerVariables(['HTTP_HOST' => config('app.admin_domain')])
         ->patchJson("/api/v1/admin/tenants/{$tenant->id}/activate")
         ->assertOk()
         ->assertJsonPath('data.status', 'active');
});

it('rejects tenant creation with duplicate domain', function (): void {
    Tenant::factory()->create(['domain' => 'dup.pms.test']);

    $this->withServerVariables(['HTTP_HOST' => config('app.admin_domain')])
         ->postJson('/api/v1/admin/tenants', [
             'name'   => 'Another',
             'domain' => 'dup.pms.test',
         ])
         ->assertUnprocessable()
         ->assertJsonValidationErrors(['domain']);
});
```

- [x] **Step 3.2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/SuperAdmin/TenantManagementTest.php
```

Expected: FAIL.

- [x] **Step 3.3: Create TenantData DTO**

Create `app/Modules/SuperAdmin/Data/TenantData.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Data;

use Spatie\LaravelData\Data;

class TenantData extends Data
{
    public function __construct(
        public readonly string  $name,
        public readonly string  $domain,
        public readonly string  $status,
        public readonly ?string $contact_name  = null,
        public readonly ?string $contact_email = null,
        public readonly ?string $contact_phone = null,
        public readonly ?int    $plan_id       = null,
    ) {}
}
```

- [x] **Step 3.4: Create StoreTenantRequest**

Create `app/Modules/SuperAdmin/Requests/StoreTenantRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:200'],
            'domain'        => ['required', 'string', 'max:255', 'unique:landlord.tenants,domain'],
            'status'        => ['sometimes', 'in:pending,active,trial,suspended'],
            'contact_name'  => ['nullable', 'string', 'max:200'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'plan_id'       => ['nullable', 'integer', 'exists:landlord.subscription_plans,id'],
        ];
    }
}
```

- [x] **Step 3.5: Create TenantResource**

Create `app/Modules/SuperAdmin/Resources/TenantResource.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'domain'        => $this->domain,
            'database'      => $this->database,
            'status'        => $this->status,
            'is_active'     => $this->isActive(),
            'is_on_trial'   => $this->isOnTrial(),
            'trial_ends_at' => $this->trial_ends_at,
            'contact_name'  => $this->contact_name,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'plan_id'       => $this->plan_id,
            'created_at'    => $this->created_at,
        ];
    }
}
```

- [x] **Step 3.6: Create tenant Actions**

Create `app/Modules/SuperAdmin/Actions/CreateTenantAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Actions;

use App\Models\Tenant;
use App\Modules\SuperAdmin\Data\TenantData;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class CreateTenantAction
{
    public function execute(TenantData $data): Tenant
    {
        $slug   = Str::slug($data->name);
        $dbName = 'pms_' . Str::replace('-', '_', $slug);

        $tenant = Tenant::create([
            'name'          => $data->name,
            'slug'          => $slug,
            'domain'        => $data->domain,
            'database'      => $dbName,
            'status'        => $data->status,
            'contact_name'  => $data->contact_name,
            'contact_email' => $data->contact_email,
            'contact_phone' => $data->contact_phone,
            'plan_id'       => $data->plan_id,
        ]);

        // Provision in background job in production; direct call for now
        Artisan::call('tenant:create', [
            'name'   => $data->name,
            'domain' => $data->domain,
        ]);

        return $tenant;
    }
}
```

Create `app/Modules/SuperAdmin/Actions/SuspendTenantAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Actions;

use App\Models\Tenant;

class SuspendTenantAction
{
    public function execute(Tenant $tenant): Tenant
    {
        $tenant->update(['status' => 'suspended']);
        return $tenant->fresh();
    }
}
```

Create `app/Modules/SuperAdmin/Actions/ActivateTenantAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Actions;

use App\Models\Tenant;

class ActivateTenantAction
{
    public function execute(Tenant $tenant): Tenant
    {
        $tenant->update(['status' => 'active']);
        return $tenant->fresh();
    }
}
```

- [x] **Step 3.7: Create TenantService**

Create `app/Modules/SuperAdmin/Services/TenantService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Services;

use App\Models\Tenant;
use App\Modules\SuperAdmin\Actions\ActivateTenantAction;
use App\Modules\SuperAdmin\Actions\CreateTenantAction;
use App\Modules\SuperAdmin\Actions\SuspendTenantAction;
use App\Modules\SuperAdmin\Data\TenantData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class TenantService
{
    public function __construct(
        private CreateTenantAction   $createAction,
        private SuspendTenantAction  $suspendAction,
        private ActivateTenantAction $activateAction,
    ) {}

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Tenant::on('landlord')->withTrashed(false)->latest('id');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('domain', 'like', "%{$filters['search']}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function findOrFail(int $id): Tenant
    {
        return Tenant::on('landlord')->findOrFail($id);
    }

    public function create(TenantData $data): Tenant
    {
        return $this->createAction->execute($data);
    }

    public function update(Tenant $tenant, TenantData $data): Tenant
    {
        $tenant->update([
            'name'          => $data->name,
            'contact_name'  => $data->contact_name,
            'contact_email' => $data->contact_email,
            'contact_phone' => $data->contact_phone,
            'plan_id'       => $data->plan_id,
        ]);
        return $tenant->fresh();
    }

    public function suspend(Tenant $tenant): Tenant
    {
        return $this->suspendAction->execute($tenant);
    }

    public function activate(Tenant $tenant): Tenant
    {
        return $this->activateAction->execute($tenant);
    }
}
```

- [x] **Step 3.8: Create Tenant API Controller**

Create `app/Modules/SuperAdmin/Controllers/Api/V1/TenantController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Data\TenantData;
use App\Modules\SuperAdmin\Requests\StoreTenantRequest;
use App\Modules\SuperAdmin\Resources\TenantResource;
use App\Modules\SuperAdmin\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(private readonly TenantService $service) {}

    public function index(Request $request): JsonResponse
    {
        $tenants = $this->service->paginate(
            filters: $request->only(['status', 'search']),
            perPage: (int) $request->get('per_page', 20),
        );

        return response()->json([
            'status'  => 1,
            'data'    => [
                'items'      => TenantResource::collection($tenants),
                'pagination' => [
                    'current_page' => $tenants->currentPage(),
                    'per_page'     => $tenants->perPage(),
                    'total'        => $tenants->total(),
                    'last_page'    => $tenants->lastPage(),
                ],
            ],
            'message' => 'Tenants fetched successfully',
        ]);
    }

    public function store(StoreTenantRequest $request): JsonResponse
    {
        $tenant = $this->service->create(TenantData::from($request->validated()));

        return response()->json([
            'status'  => 1,
            'data'    => new TenantResource($tenant),
            'message' => 'Tenant created and provisioned successfully',
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'status'  => 1,
            'data'    => new TenantResource($this->service->findOrFail($id)),
            'message' => 'Tenant fetched successfully',
        ]);
    }

    public function update(StoreTenantRequest $request, int $id): JsonResponse
    {
        $tenant  = $this->service->findOrFail($id);
        $updated = $this->service->update($tenant, TenantData::from($request->validated()));

        return response()->json([
            'status'  => 1,
            'data'    => new TenantResource($updated),
            'message' => 'Tenant updated successfully',
        ]);
    }

    public function suspend(int $id): JsonResponse
    {
        $tenant = $this->service->suspend($this->service->findOrFail($id));

        return response()->json([
            'status'  => 1,
            'data'    => new TenantResource($tenant),
            'message' => 'Tenant suspended',
        ]);
    }

    public function activate(int $id): JsonResponse
    {
        $tenant = $this->service->activate($this->service->findOrFail($id));

        return response()->json([
            'status'  => 1,
            'data'    => new TenantResource($tenant),
            'message' => 'Tenant activated',
        ]);
    }
}
```

- [ ] **Step 3.9: Run tests**

```bash
php artisan test tests/Feature/SuperAdmin/TenantManagementTest.php
```

Expected: 5 tests PASS.

- [ ] **Step 3.10: Commit**

```bash
git add app/Modules/SuperAdmin/ app/Models/SubscriptionPlan.php \
        tests/Feature/SuperAdmin/TenantManagementTest.php
git commit -m "feat: add Tenant management CRUD with suspend/activate actions"
```

---

## Task 4: Super Admin Layout + Inertia Pages

**Files:**
- Create: `resources/js/Layouts/SuperAdminLayout.vue`
- Create: `resources/js/Pages/SuperAdmin/Dashboard/Index.vue`
- Create: `resources/js/Pages/SuperAdmin/Tenants/Index.vue`
- Create: `resources/js/Pages/SuperAdmin/SubscriptionPlans/Index.vue`
- Create: `resources/js/Types/SuperAdmin/tenant.ts`
- Create: `resources/js/Utils/Mappers/tenant.ts`
- Create: `resources/js/Stores/SuperAdmin/tenantStore.ts`

- [x] **Step 4.1: Create TypeScript types**

Create `resources/js/Types/SuperAdmin/tenant.ts`:

```typescript
export interface Tenant {
  id: number
  name: string
  slug: string
  domain: string
  database: string
  status: 'pending' | 'active' | 'trial' | 'suspended' | 'cancelled'
  isActive: boolean
  isOnTrial: boolean
  trialEndsAt: string | null
  contactName: string | null
  contactEmail: string | null
  contactPhone: string | null
  planId: number | null
  createdAt: string
}

export interface TenantFilters {
  status: string
  search: string
  perPage: number
}

export interface TenantPagination {
  currentPage: number
  perPage: number
  total: number
  lastPage: number
}

export interface CreateTenantDto {
  name: string
  domain: string
  status: string
  contactName: string
  contactEmail: string
  contactPhone: string
  planId: number | null
}
```

- [x] **Step 4.2: Create tenant mapper**

Create `resources/js/Utils/Mappers/tenant.ts`:

```typescript
import type { Tenant, CreateTenantDto, TenantPagination } from '@/Types/SuperAdmin/tenant'

export function mapTenantApiToTenant(api: Record<string, unknown>): Tenant {
  return {
    id:           api.id as number,
    name:         api.name as string,
    slug:         api.slug as string,
    domain:       api.domain as string,
    database:     api.database as string,
    status:       api.status as Tenant['status'],
    isActive:     api.is_active as boolean,
    isOnTrial:    api.is_on_trial as boolean,
    trialEndsAt:  api.trial_ends_at as string | null,
    contactName:  api.contact_name as string | null,
    contactEmail: api.contact_email as string | null,
    contactPhone: api.contact_phone as string | null,
    planId:       api.plan_id as number | null,
    createdAt:    api.created_at as string,
  }
}

export function mapCreateTenantToApi(dto: CreateTenantDto): Record<string, unknown> {
  return {
    name:          dto.name,
    domain:        dto.domain,
    status:        dto.status,
    contact_name:  dto.contactName,
    contact_email: dto.contactEmail,
    contact_phone: dto.contactPhone,
    plan_id:       dto.planId,
  }
}

export function mapTenantPaginationApiToPagination(api: Record<string, unknown>): TenantPagination {
  return {
    currentPage: api.current_page as number,
    perPage:     api.per_page as number,
    total:       api.total as number,
    lastPage:    api.last_page as number,
  }
}
```

- [x] **Step 4.3: Create tenant Pinia store**

Create `resources/js/Stores/SuperAdmin/tenantStore.ts`:

```typescript
import { defineStore } from 'pinia'
import apiClient from '@/Services/apiClient'
import { getErrorMessage } from '@/Helpers/error'
import type { ApiResponse } from '@/Types/api'
import type { Tenant, TenantFilters, TenantPagination, CreateTenantDto } from '@/Types/SuperAdmin/tenant'
import {
  mapTenantApiToTenant,
  mapCreateTenantToApi,
  mapTenantPaginationApiToPagination,
} from '@/Utils/Mappers/tenant'

const DEFAULT_PAGINATION: TenantPagination = { currentPage: 1, perPage: 20, total: 0, lastPage: 1 }
const DEFAULT_FILTERS: TenantFilters = { status: '', search: '', perPage: 20 }

export const useTenantsStore = defineStore('admin-tenants', {
  state: () => ({
    tenants:         [] as Tenant[],
    selectedTenant:  null as Tenant | null,
    pagination:      { ...DEFAULT_PAGINATION },
    filters:         { ...DEFAULT_FILTERS },
    loading:         false,
    loadingList:     false,
    loadingDetail:   false,
    error:           null as string | null,
  }),

  actions: {
    async fetchAll(filters?: Partial<TenantFilters>) {
      this.loadingList = true
      this.error = null
      if (filters) Object.assign(this.filters, filters)
      try {
        const params = {
          page:     this.pagination.currentPage,
          per_page: this.filters.perPage,
          status:   this.filters.status || undefined,
          search:   this.filters.search || undefined,
        }
        const res = await apiClient.get<ApiResponse>('/api/v1/admin/tenants', { params })
        this.tenants    = (res.data.data.items as Record<string, unknown>[]).map(mapTenantApiToTenant)
        this.pagination = mapTenantPaginationApiToPagination(res.data.data.pagination)
      } catch (err) {
        this.error = getErrorMessage(err)
      } finally {
        this.loadingList = false
      }
    },

    async create(dto: CreateTenantDto): Promise<Tenant> {
      this.loading = true
      this.error = null
      try {
        const res = await apiClient.post<ApiResponse>('/api/v1/admin/tenants', mapCreateTenantToApi(dto))
        const tenant = mapTenantApiToTenant(res.data.data as Record<string, unknown>)
        this.tenants.unshift(tenant)
        return tenant
      } catch (err) {
        this.error = getErrorMessage(err)
        throw err
      } finally {
        this.loading = false
      }
    },

    async suspend(id: number): Promise<void> {
      await apiClient.patch(`/api/v1/admin/tenants/${id}/suspend`)
      const t = this.tenants.find(x => x.id === id)
      if (t) t.status = 'suspended'
    },

    async activate(id: number): Promise<void> {
      await apiClient.patch(`/api/v1/admin/tenants/${id}/activate`)
      const t = this.tenants.find(x => x.id === id)
      if (t) t.status = 'active'
    },
  },
})
```

- [x] **Step 4.4: Create SuperAdmin layout**

Create `resources/js/Layouts/SuperAdminLayout.vue`:

```vue
<script setup lang="ts">
import { Head } from '@inertiajs/vue3'

const nav = [
  { label: 'Dashboard',  href: '/dashboard' },
  { label: 'Tenants',    href: '/tenants' },
  { label: 'Plans',      href: '/plans' },
  { label: 'Billing',    href: '/billing' },
  { label: 'Reports',    href: '/reports' },
  { label: 'Settings',   href: '/settings' },
]
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col">
      <div class="p-6 border-b border-gray-700">
        <span class="text-xl font-bold tracking-tight">PMS Admin</span>
      </div>
      <nav class="flex-1 p-4 space-y-1">
        <a
          v-for="item in nav"
          :key="item.href"
          :href="item.href"
          class="block px-4 py-2 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition-colors"
        >
          {{ item.label }}
        </a>
      </nav>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
        <slot name="header" />
        <span class="text-sm text-gray-500">Super Admin</span>
      </header>
      <main class="flex-1 overflow-y-auto p-8">
        <slot />
      </main>
    </div>
  </div>
</template>
```

- [x] **Step 4.5: Create Dashboard Index page**

Create `resources/js/Pages/SuperAdmin/Dashboard/Index.vue`:

```vue
<script setup lang="ts">
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue'

defineOptions({ layout: SuperAdminLayout })

const props = defineProps<{
  stats: {
    totalActiveTenants: number
    totalActiveProperties: number
    pendingInvoices: number
    trialExpiringSoon: number
  }
}>()
</script>

<template>
  <Head title="Platform Dashboard" />

  <template #header>
    <h1 class="text-2xl font-semibold text-gray-800">Platform Dashboard</h1>
  </template>

  <div class="grid grid-cols-4 gap-6">
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <p class="text-sm text-gray-500">Active Tenants</p>
      <p class="text-3xl font-bold text-gray-900 mt-2">{{ props.stats.totalActiveTenants }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <p class="text-sm text-gray-500">Active Properties</p>
      <p class="text-3xl font-bold text-gray-900 mt-2">{{ props.stats.totalActiveProperties }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <p class="text-sm text-gray-500">Pending Invoices</p>
      <p class="text-3xl font-bold text-yellow-600 mt-2">{{ props.stats.pendingInvoices }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <p class="text-sm text-gray-500">Trials Expiring (7d)</p>
      <p class="text-3xl font-bold text-red-600 mt-2">{{ props.stats.trialExpiringSoon }}</p>
    </div>
  </div>
</template>
```

- [x] **Step 4.6: Create DashboardController (Web)**

Create `app/Modules/SuperAdmin/Controllers/Web/DashboardController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('SuperAdmin/Dashboard/Index', [
            'stats' => [
                'totalActiveTenants'     => Tenant::on('landlord')->where('status', 'active')->count(),
                'totalActiveProperties'  => 0, // expanded in later tasks
                'pendingInvoices'        => \App\Models\Invoice::on('landlord')->where('status', 'sent')->count(),
                'trialExpiringSoon'      => Tenant::on('landlord')
                    ->where('status', 'trial')
                    ->where('trial_ends_at', '<=', now()->addDays(7))
                    ->count(),
            ],
        ]);
    }
}
```

- [ ] **Step 4.7: Run entire test suite**

```bash
composer run test
```

Expected: all tests PASS.

- [ ] **Step 4.8: Commit**

```bash
git add resources/js/Layouts/SuperAdminLayout.vue \
        resources/js/Pages/SuperAdmin/ \
        resources/js/Stores/SuperAdmin/ \
        resources/js/Types/SuperAdmin/ \
        resources/js/Utils/Mappers/tenant.ts \
        app/Modules/SuperAdmin/Controllers/Web/
git commit -m "feat: add SuperAdmin layout, Dashboard page, Tenant list page with Pinia store"
```

---

## Phase 1 Completion Checklist

Run these checks before starting Phase 2:

- [ ] `php artisan test` → all tests PASS
- [ ] `./vendor/bin/pint` → no violations
- [ ] Visit `admin.pms.test/dashboard` → Super Admin dashboard renders
- [ ] Visit `admin.pms.test/tenants` → Tenant list renders with pagination
- [ ] POST `/api/v1/admin/tenants` → creates tenant and provisions DB
- [ ] PATCH `/api/v1/admin/tenants/{id}/suspend` → status changes to `suspended`
- [ ] A suspended tenant accessing their PMS domain → returns 403
- [ ] GET `/api/v1/admin/plans` → lists subscription plans
