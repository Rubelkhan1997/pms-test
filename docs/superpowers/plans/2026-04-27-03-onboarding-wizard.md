# Tenant Onboarding Wizard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the first-login onboarding wizard that guides a new tenant through 3 steps: Property Setup, Room Type & Inventory Generation, and Rate Plan & Policy Configuration — creating the foundational data every PMS operation depends on.

**Architecture:** `EnsurePropertyOnboarded` middleware redirects any authenticated user with 0 properties to `/onboarding/property/create`. A stepped Vue wizard collects data and submits each step independently. The backend validates each step via FormRequest and stores data in the tenant DB. On wizard completion, the user lands on the main Dashboard.

**Tech Stack:** Laravel 13, Inertia.js v2, Vue 3 + TypeScript, Pinia, Pest PHP.

**Depends on:** Phase 0 (Tenancy Infrastructure) + Phase 1 (Super Admin Panel) fully complete.

---

## File Map

| Action | File | Responsibility |
|---|---|---|
| Create | `database/migrations/tenant/2026_04_27_100000_create_properties_table.php` | Full property schema |
| Create | `database/migrations/tenant/2026_04_27_100100_create_room_types_table.php` | Room type templates |
| Create | `database/migrations/tenant/2026_04_27_100200_create_rooms_table.php` | Physical room inventory |
| Create | `database/migrations/tenant/2026_04_27_100300_create_cancellation_policies_table.php` | |
| Create | `database/migrations/tenant/2026_04_27_100400_create_pricing_profiles_table.php` | OBP profiles |
| Create | `database/migrations/tenant/2026_04_27_100500_create_rate_plans_table.php` | |
| Create | `database/migrations/tenant/2026_04_27_100600_create_rate_restrictions_table.php` | |
| Create | `app/Modules/FrontDesk/Models/Property.php` | |
| Create | `app/Modules/FrontDesk/Models/RoomType.php` | |
| Create | `app/Modules/FrontDesk/Models/Room.php` | (replaces/expands current rooms model) |
| Create | `app/Modules/RateAvailability/Models/CancellationPolicy.php` | |
| Create | `app/Modules/RateAvailability/Models/PricingProfile.php` | |
| Create | `app/Modules/RateAvailability/Models/RatePlan.php` | |
| Create | `app/Modules/FrontDesk/Controllers/Api/V1/PropertyController.php` | |
| Create | `app/Modules/FrontDesk/Controllers/Api/V1/RoomTypeController.php` | |
| Create | `app/Modules/FrontDesk/Controllers/Api/V1/RoomController.php` | (expand existing) |
| Create | `app/Modules/FrontDesk/Services/PropertyService.php` | |
| Create | `app/Modules/FrontDesk/Services/RoomTypeService.php` | |
| Create | `app/Modules/FrontDesk/Actions/CreatePropertyAction.php` | |
| Create | `app/Modules/FrontDesk/Actions/GenerateRoomInventoryAction.php` | Auto/manual room gen |
| Create | `app/Modules/FrontDesk/Data/PropertyData.php` | |
| Create | `app/Modules/FrontDesk/Data/RoomTypeData.php` | |
| Create | `app/Modules/FrontDesk/Requests/StorePropertyRequest.php` | |
| Create | `app/Modules/FrontDesk/Requests/StoreRoomTypeRequest.php` | |
| Create | `app/Modules/FrontDesk/Resources/PropertyResource.php` | |
| Create | `app/Modules/FrontDesk/Resources/RoomTypeResource.php` | |
| Create | `resources/js/Pages/Onboarding/Property/Create.vue` | Step 1 page |
| Create | `resources/js/Pages/Onboarding/RoomType/Create.vue` | Step 2 page |
| Create | `resources/js/Pages/Onboarding/RatePlan/Create.vue` | Step 3 page |
| Create | `resources/js/Stores/FrontDesk/propertyStore.ts` | |
| Create | `resources/js/Stores/FrontDesk/roomTypeStore.ts` | |
| Create | `resources/js/Types/FrontDesk/property.ts` | |
| Create | `resources/js/Types/FrontDesk/roomType.ts` | |
| Create | `resources/js/Utils/Mappers/property.ts` | |
| Create | `resources/js/Utils/Mappers/roomType.ts` | |
| Create | `tests/Feature/Onboarding/PropertySetupTest.php` | |
| Create | `tests/Feature/Onboarding/RoomInventoryTest.php` | |
| Create | `tests/Feature/Onboarding/RatePlanSetupTest.php` | |

---

## Task 1: Database Migrations

- [ ] **Step 1.1: Create properties migration**

Create `database/migrations/tenant/2026_04_27_100000_create_properties_table.php`:

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
        Schema::create('properties', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['hotel', 'resort', 'apartment', 'villa', 'hostel'])->default('hotel');
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('featured_image_path')->nullable();
            $table->json('gallery_paths')->nullable();
            $table->unsignedInteger('number_of_rooms')->default(0);
            $table->char('country', 2)->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('area')->nullable();
            $table->string('street')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('timezone')->default('UTC');
            $table->char('currency', 3)->default('USD');
            $table->time('check_in_time')->default('14:00:00');
            $table->time('check_out_time')->default('12:00:00');
            $table->json('child_policy')->nullable();
            $table->json('pet_policy')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->date('business_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
```

- [ ] **Step 1.2: Create room_types migration**

Create `database/migrations/tenant/2026_04_27_100100_create_room_types_table.php`:

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
        Schema::create('room_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->enum('type', ['room', 'suite', 'cottage', 'villa', 'dormitory'])->default('room');
            $table->string('floor')->nullable();
            $table->unsignedSmallInteger('max_occupancy')->default(2);
            $table->unsignedSmallInteger('adult_occupancy')->default(2);
            $table->unsignedSmallInteger('num_bedrooms')->default(1);
            $table->unsignedSmallInteger('num_bathrooms')->default(1);
            $table->decimal('area_sqm', 8, 2)->nullable();
            $table->json('bed_types')->nullable();
            $table->decimal('base_rate', 10, 2)->default(0);
            $table->json('amenities')->nullable();
            $table->json('gallery_paths')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['property_id', 'code']);
            $table->index('property_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
```

- [ ] **Step 1.3: Create rooms (physical inventory) migration**

Create `database/migrations/tenant/2026_04_27_100200_create_rooms_table.php`:

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
        // Drop old rooms table if it exists from earlier scaffold
        Schema::dropIfExists('rooms');

        Schema::create('rooms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('identifier');                   // "101", "Cottage-3"
            $table->string('floor')->nullable();
            $table->json('view_types')->nullable();          // ["Beach View","Pool View"]
            $table->unsignedSmallInteger('num_bedrooms')->nullable();
            $table->unsignedSmallInteger('num_bathrooms')->nullable();
            $table->decimal('area_sqm', 8, 2)->nullable();
            $table->json('bed_types')->nullable();
            $table->enum('status', [
                'vacant_clean', 'vacant_dirty', 'occupied', 'out_of_order', 'out_of_service',
            ])->default('vacant_clean');
            $table->enum('hk_status', [
                'clean', 'dirty', 'hk_assigned', 'inspected', 'blocked',
            ])->default('clean');
            $table->boolean('usable')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['property_id', 'identifier']);
            $table->index(['property_id', 'status']);
            $table->index(['property_id', 'room_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
```

- [ ] **Step 1.4: Create rate plan supporting tables**

Create `database/migrations/tenant/2026_04_27_100300_create_cancellation_policies_table.php`:

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
        Schema::create('cancellation_policies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('deadline_days')->default(0);
            $table->enum('penalty_type', ['none', 'first_night', 'percentage', 'full_stay'])->default('none');
            $table->decimal('penalty_value', 8, 2)->default(0);
            $table->enum('no_show_penalty_type', ['none', 'first_night', 'full_stay', 'fixed'])->default('none');
            $table->decimal('no_show_penalty_value', 8, 2)->default(0);
            $table->timestamps();
            $table->index('property_id');
        });

        Schema::create('pricing_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('standard_occupancy')->default(2);
            $table->enum('single_discount_type', ['fixed', 'percentage'])->nullable();
            $table->decimal('single_discount_value', 8, 2)->default(0);
            $table->decimal('extra_adult_fee', 8, 2)->default(0);
            $table->json('child_buckets')->nullable();
            $table->timestamps();
            $table->index('property_id');
        });

        Schema::create('rate_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->enum('type', ['base', 'derived'])->default('base');
            $table->enum('meal_plan', ['RO', 'BB', 'HB', 'FB', 'AI'])->default('RO');
            $table->foreignId('cancellation_policy_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pricing_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_rate_plan_id')->nullable()->constrained('rate_plans')->nullOnDelete();
            $table->enum('derived_modifier_type', ['plus', 'minus'])->nullable();
            $table->decimal('derived_modifier_value', 8, 2)->nullable();
            $table->unsignedInteger('release_period_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['property_id', 'code']);
            $table->index(['property_id', 'is_active']);
        });

        Schema::create('rate_restrictions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedSmallInteger('min_stay')->default(1);
            $table->unsignedSmallInteger('max_stay')->nullable();
            $table->boolean('closed_to_arrival')->default(false);
            $table->boolean('closed_to_departure')->default(false);
            $table->boolean('stop_sell')->default(false);
            $table->timestamps();
            $table->unique(['property_id', 'date', 'rate_plan_id', 'room_type_id']);
            $table->index(['property_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_restrictions');
        Schema::dropIfExists('rate_plans');
        Schema::dropIfExists('pricing_profiles');
        Schema::dropIfExists('cancellation_policies');
    }
};
```

- [ ] **Step 1.5: Run tenant migrations**

```bash
# Against a test tenant DB (replace 'pms_test' with your test tenant DB):
php artisan migrate --path=database/migrations/tenant --database=mysql --force
```

Expected: properties, room_types, rooms, cancellation_policies, pricing_profiles, rate_plans, rate_restrictions tables created.

- [ ] **Step 1.6: Commit**

```bash
git add database/migrations/tenant/2026_04_27_1*
git commit -m "feat: add property, room_type, room, rate plan schema migrations"
```

---

## Task 2: Property CRUD Backend

- [ ] **Step 2.1: Write failing tests**

Create `tests/Feature/Onboarding/PropertySetupTest.php`:

```php
<?php

declare(strict_types=1);

it('creates a new property with required fields', function (): void {
    $payload = [
        'name'           => 'Ocean Breeze Hotel',
        'type'           => 'hotel',
        'number_of_rooms'=> 50,
        'timezone'       => 'Asia/Dhaka',
        'currency'       => 'BDT',
        'check_in_time'  => '14:00',
        'check_out_time' => '12:00',
    ];

    $this->actingAs(\App\Models\User::factory()->create())
         ->postJson('/api/v1/properties', $payload)
         ->assertCreated()
         ->assertJsonPath('status', 1)
         ->assertJsonPath('data.name', 'Ocean Breeze Hotel');
});

it('auto-detects timezone and currency from country', function (): void {
    $payload = [
        'name'    => 'Dhaka Grand',
        'type'    => 'hotel',
        'country' => 'BD',
        'number_of_rooms' => 20,
    ];

    $response = $this->actingAs(\App\Models\User::factory()->create())
                     ->postJson('/api/v1/properties', $payload)
                     ->assertCreated();

    expect($response->json('data.timezone'))->toBe('Asia/Dhaka')
        ->and($response->json('data.currency'))->toBe('BDT');
});

it('fetches the current property', function (): void {
    \App\Modules\FrontDesk\Models\Property::factory()->create(['name' => 'Test Property']);

    $this->actingAs(\App\Models\User::factory()->create())
         ->getJson('/api/v1/properties/1')
         ->assertOk()
         ->assertJsonPath('data.name', 'Test Property');
});
```

- [ ] **Step 2.2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/Onboarding/PropertySetupTest.php
```

Expected: FAIL.

- [ ] **Step 2.3: Create Property model**

Create `app/Modules/FrontDesk/Models/Property.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'type', 'description',
        'logo_path', 'featured_image_path', 'gallery_paths',
        'number_of_rooms', 'country', 'state', 'city', 'area',
        'street', 'postal_code', 'latitude', 'longitude',
        'phone', 'email', 'timezone', 'currency',
        'check_in_time', 'check_out_time',
        'child_policy', 'pet_policy', 'status', 'business_date',
    ];

    protected $casts = [
        'gallery_paths' => 'array',
        'child_policy'  => 'array',
        'pet_policy'    => 'array',
        'business_date' => 'date',
        'check_in_time' => 'string',
        'check_out_time'=> 'string',
    ];
}
```

- [ ] **Step 2.4: Create PropertyData DTO**

Create `app/Modules/FrontDesk/Data/PropertyData.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Data;

use Spatie\LaravelData\Data;

class PropertyData extends Data
{
    public function __construct(
        public readonly string  $name,
        public readonly string  $type,
        public readonly int     $number_of_rooms,
        public readonly ?string $description    = null,
        public readonly ?string $country        = null,
        public readonly ?string $state          = null,
        public readonly ?string $city           = null,
        public readonly ?string $street         = null,
        public readonly ?string $postal_code    = null,
        public readonly ?float  $latitude       = null,
        public readonly ?float  $longitude      = null,
        public readonly ?string $phone          = null,
        public readonly ?string $email          = null,
        public readonly string  $timezone       = 'UTC',
        public readonly string  $currency       = 'USD',
        public readonly string  $check_in_time  = '14:00',
        public readonly string  $check_out_time = '12:00',
        public readonly ?array  $child_policy   = null,
        public readonly ?array  $pet_policy     = null,
    ) {}
}
```

- [ ] **Step 2.5: Create StorePropertyRequest**

Create `app/Modules/FrontDesk/Requests/StorePropertyRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:200'],
            'type'            => ['required', 'in:hotel,resort,apartment,villa,hostel'],
            'number_of_rooms' => ['required', 'integer', 'min:1', 'max:10000'],
            'description'     => ['nullable', 'string'],
            'country'         => ['nullable', 'string', 'size:2'],
            'state'           => ['nullable', 'string', 'max:100'],
            'city'            => ['nullable', 'string', 'max:100'],
            'street'          => ['nullable', 'string', 'max:255'],
            'postal_code'     => ['nullable', 'string', 'max:20'],
            'latitude'        => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'       => ['nullable', 'numeric', 'between:-180,180'],
            'phone'           => ['nullable', 'string', 'max:30'],
            'email'           => ['nullable', 'email'],
            'timezone'        => ['nullable', 'timezone'],
            'currency'        => ['nullable', 'string', 'size:3'],
            'check_in_time'   => ['nullable', 'date_format:H:i'],
            'check_out_time'  => ['nullable', 'date_format:H:i'],
        ];
    }
}
```

- [ ] **Step 2.6: Create CreatePropertyAction**

Create `app/Modules/FrontDesk/Actions/CreatePropertyAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

use App\Modules\FrontDesk\Data\PropertyData;
use App\Modules\FrontDesk\Models\Property;
use Illuminate\Support\Str;

class CreatePropertyAction
{
    // Country → [timezone, currency] defaults
    private const COUNTRY_DEFAULTS = [
        'BD' => ['Asia/Dhaka', 'BDT'],
        'US' => ['America/New_York', 'USD'],
        'GB' => ['Europe/London', 'GBP'],
        'AE' => ['Asia/Dubai', 'AED'],
        'SG' => ['Asia/Singapore', 'SGD'],
        'IN' => ['Asia/Kolkata', 'INR'],
        // extend as needed
    ];

    public function execute(PropertyData $data): Property
    {
        [$timezone, $currency] = $this->resolveLocale(
            $data->country,
            $data->timezone,
            $data->currency,
        );

        return Property::create([
            ...$data->toArray(),
            'slug'          => Str::slug($data->name),
            'timezone'      => $timezone,
            'currency'      => $currency,
            'business_date' => now()->toDateString(),
        ]);
    }

    private function resolveLocale(?string $country, string $timezone, string $currency): array
    {
        if ($country && isset(self::COUNTRY_DEFAULTS[$country])) {
            [$defaultTz, $defaultCurrency] = self::COUNTRY_DEFAULTS[$country];
            return [
                $timezone === 'UTC' ? $defaultTz : $timezone,
                $currency === 'USD' ? $defaultCurrency : $currency,
            ];
        }
        return [$timezone, $currency];
    }
}
```

- [ ] **Step 2.7: Create PropertyService**

Create `app/Modules/FrontDesk/Services/PropertyService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Services;

use App\Modules\FrontDesk\Actions\CreatePropertyAction;
use App\Modules\FrontDesk\Data\PropertyData;
use App\Modules\FrontDesk\Models\Property;

readonly class PropertyService
{
    public function __construct(private CreatePropertyAction $createAction) {}

    public function findOrFail(int $id): Property
    {
        return Property::findOrFail($id);
    }

    public function create(PropertyData $data): Property
    {
        return $this->createAction->execute($data);
    }

    public function update(Property $property, PropertyData $data): Property
    {
        $property->update($data->toArray());
        return $property->fresh();
    }
}
```

- [ ] **Step 2.8: Create PropertyResource**

Create `app/Modules/FrontDesk/Resources/PropertyResource.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'slug'            => $this->slug,
            'type'            => $this->type,
            'description'     => $this->description,
            'number_of_rooms' => $this->number_of_rooms,
            'country'         => $this->country,
            'city'            => $this->city,
            'timezone'        => $this->timezone,
            'currency'        => $this->currency,
            'check_in_time'   => $this->check_in_time,
            'check_out_time'  => $this->check_out_time,
            'status'          => $this->status,
            'business_date'   => $this->business_date,
            'created_at'      => $this->created_at,
        ];
    }
}
```

- [ ] **Step 2.9: Create PropertyController (API)**

Create `app/Modules/FrontDesk/Controllers/Api/V1/PropertyController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Data\PropertyData;
use App\Modules\FrontDesk\Requests\StorePropertyRequest;
use App\Modules\FrontDesk\Resources\PropertyResource;
use App\Modules\FrontDesk\Services\PropertyService;
use Illuminate\Http\JsonResponse;

class PropertyController extends Controller
{
    public function __construct(private readonly PropertyService $service) {}

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'status'  => 1,
            'data'    => new PropertyResource($this->service->findOrFail($id)),
            'message' => 'Property fetched',
        ]);
    }

    public function store(StorePropertyRequest $request): JsonResponse
    {
        $property = $this->service->create(PropertyData::from($request->validated()));

        return response()->json([
            'status'  => 1,
            'data'    => new PropertyResource($property),
            'message' => 'Property created successfully',
        ], 201);
    }

    public function update(StorePropertyRequest $request, int $id): JsonResponse
    {
        $property = $this->service->findOrFail($id);
        $updated  = $this->service->update($property, PropertyData::from($request->validated()));

        return response()->json([
            'status'  => 1,
            'data'    => new PropertyResource($updated),
            'message' => 'Property updated successfully',
        ]);
    }
}
```

- [ ] **Step 2.10: Add property routes to routes/tenant-api.php (or routes/api.php)**

In `routes/api.php` inside the `auth:sanctum` protected group:

```php
Route::prefix('v1')->group(function (): void {
    // ... existing routes ...
    Route::get('properties/{id}',  [PropertyController::class, 'show']);
    Route::post('properties',      [PropertyController::class, 'store']);
    Route::put('properties/{id}',  [PropertyController::class, 'update']);
});
```

- [ ] **Step 2.11: Run tests**

```bash
php artisan test tests/Feature/Onboarding/PropertySetupTest.php
```

Expected: 3 tests PASS.

- [ ] **Step 2.12: Commit**

```bash
git add app/Modules/FrontDesk/ tests/Feature/Onboarding/PropertySetupTest.php routes/api.php
git commit -m "feat: add Property CRUD with country-based locale auto-detection"
```

---

## Task 3: Room Type & Inventory Generation

- [ ] **Step 3.1: Write failing tests**

Create `tests/Feature/Onboarding/RoomInventoryTest.php`:

```php
<?php

declare(strict_types=1);

use App\Modules\FrontDesk\Models\Property;
use App\Modules\FrontDesk\Models\Room;
use App\Modules\FrontDesk\Models\RoomType;

it('creates room type and auto-generates room inventory', function (): void {
    $property = Property::factory()->create();

    $this->actingAs(\App\Models\User::factory()->create())
         ->postJson('/api/v1/room-types', [
             'property_id'     => $property->id,
             'name'            => 'Deluxe King',
             'code'            => 'DKNG',
             'max_occupancy'   => 2,
             'adult_occupancy' => 2,
             'base_rate'       => 150.00,
             'room_count'      => 10,
             'numbering_mode'  => 'auto',
             'start_number'    => 101,
         ])
         ->assertCreated()
         ->assertJsonPath('status', 1);

    expect(RoomType::count())->toBe(1)
        ->and(Room::count())->toBe(10)
        ->and(Room::first()->identifier)->toBe('101');
});

it('creates rooms with manual identifiers', function (): void {
    $property = Property::factory()->create();

    $this->actingAs(\App\Models\User::factory()->create())
         ->postJson('/api/v1/room-types', [
             'property_id'         => $property->id,
             'name'                => 'Garden Cottage',
             'code'                => 'GCT',
             'max_occupancy'       => 4,
             'adult_occupancy'     => 2,
             'base_rate'           => 200.00,
             'room_count'          => 3,
             'numbering_mode'      => 'manual',
             'manual_identifiers'  => ['Cottage-A', 'Cottage-B', 'Cottage-C'],
         ])
         ->assertCreated();

    expect(Room::pluck('identifier')->toArray())
        ->toEqual(['Cottage-A', 'Cottage-B', 'Cottage-C']);
});

it('rejects duplicate room identifiers within same property', function (): void {
    $property = Property::factory()->create();
    Room::factory()->create(['property_id' => $property->id, 'identifier' => '101']);

    $this->actingAs(\App\Models\User::factory()->create())
         ->postJson('/api/v1/room-types', [
             'property_id'        => $property->id,
             'name'               => 'Standard',
             'code'               => 'STD',
             'max_occupancy'      => 2,
             'adult_occupancy'    => 2,
             'base_rate'          => 100.00,
             'room_count'         => 1,
             'numbering_mode'     => 'manual',
             'manual_identifiers' => ['101'],
         ])
         ->assertUnprocessable();
});
```

- [ ] **Step 3.2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/Onboarding/RoomInventoryTest.php
```

Expected: FAIL.

- [ ] **Step 3.3: Create RoomType model**

Create `app/Modules/FrontDesk/Models/RoomType.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    protected $fillable = [
        'property_id', 'name', 'code', 'type', 'floor',
        'max_occupancy', 'adult_occupancy', 'num_bedrooms', 'num_bathrooms',
        'area_sqm', 'bed_types', 'base_rate', 'amenities', 'gallery_paths', 'is_active',
    ];

    protected $casts = [
        'bed_types'    => 'array',
        'amenities'    => 'array',
        'gallery_paths'=> 'array',
        'is_active'    => 'boolean',
        'base_rate'    => 'decimal:2',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
```

- [ ] **Step 3.4: Create GenerateRoomInventoryAction**

Create `app/Modules/FrontDesk/Actions/GenerateRoomInventoryAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

use App\Modules\FrontDesk\Models\Room;
use App\Modules\FrontDesk\Models\RoomType;
use Illuminate\Validation\ValidationException;

class GenerateRoomInventoryAction
{
    /**
     * @param  array<string>|null  $manualIdentifiers
     */
    public function execute(
        RoomType $roomType,
        int      $roomCount,
        string   $mode,              // 'auto' | 'manual'
        int      $startNumber = 101,
        ?array   $manualIdentifiers = null,
    ): void {
        $identifiers = $mode === 'auto'
            ? $this->generateAutoIdentifiers($roomCount, $startNumber)
            : $manualIdentifiers ?? [];

        $this->validateUniqueness($roomType->property_id, $identifiers);

        $rooms = array_map(
            fn (string $id): array => [
                'property_id'  => $roomType->property_id,
                'room_type_id' => $roomType->id,
                'identifier'   => $id,
                'floor'        => $roomType->floor,
                'status'       => 'vacant_clean',
                'hk_status'    => 'clean',
                'usable'       => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            $identifiers,
        );

        Room::insert($rooms);
    }

    /** @return array<string> */
    private function generateAutoIdentifiers(int $count, int $start): array
    {
        return array_map(
            static fn (int $i): string => (string) ($start + $i),
            range(0, $count - 1),
        );
    }

    /** @param array<string> $identifiers */
    private function validateUniqueness(int $propertyId, array $identifiers): void
    {
        $existing = Room::where('property_id', $propertyId)
            ->whereIn('identifier', $identifiers)
            ->pluck('identifier')
            ->toArray();

        if (! empty($existing)) {
            throw ValidationException::withMessages([
                'manual_identifiers' => 'Room identifiers already exist: ' . implode(', ', $existing),
            ]);
        }
    }
}
```

- [ ] **Step 3.5: Create RoomTypeService**

Create `app/Modules/FrontDesk/Services/RoomTypeService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Services;

use App\Modules\FrontDesk\Actions\GenerateRoomInventoryAction;
use App\Modules\FrontDesk\Models\RoomType;
use Illuminate\Support\Facades\DB;

readonly class RoomTypeService
{
    public function __construct(
        private GenerateRoomInventoryAction $generateRoomsAction,
    ) {}

    public function createWithInventory(array $validated): RoomType
    {
        return DB::transaction(function () use ($validated): RoomType {
            $roomType = RoomType::create([
                'property_id'    => $validated['property_id'],
                'name'           => $validated['name'],
                'code'           => $validated['code'],
                'type'           => $validated['room_type'] ?? 'room',
                'floor'          => $validated['floor'] ?? null,
                'max_occupancy'  => $validated['max_occupancy'],
                'adult_occupancy'=> $validated['adult_occupancy'],
                'base_rate'      => $validated['base_rate'],
            ]);

            $this->generateRoomsAction->execute(
                roomType:          $roomType,
                roomCount:         $validated['room_count'],
                mode:              $validated['numbering_mode'],
                startNumber:       $validated['start_number'] ?? 101,
                manualIdentifiers: $validated['manual_identifiers'] ?? null,
            );

            return $roomType->load('rooms');
        });
    }
}
```

- [ ] **Step 3.6: Create StoreRoomTypeRequest**

Create `app/Modules/FrontDesk/Requests/StoreRoomTypeRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomTypeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'property_id'        => ['required', 'integer', 'exists:properties,id'],
            'name'               => ['required', 'string', 'max:100'],
            'code'               => ['required', 'string', 'max:20'],
            'max_occupancy'      => ['required', 'integer', 'min:1'],
            'adult_occupancy'    => ['required', 'integer', 'min:1'],
            'base_rate'          => ['required', 'numeric', 'min:0'],
            'room_count'         => ['required', 'integer', 'min:1', 'max:1000'],
            'numbering_mode'     => ['required', 'in:auto,manual'],
            'start_number'       => ['required_if:numbering_mode,auto', 'integer', 'min:1'],
            'manual_identifiers' => [
                'required_if:numbering_mode,manual',
                'array',
                'min:1',
            ],
            'manual_identifiers.*' => ['string', 'distinct', 'max:20'],
        ];
    }
}
```

- [ ] **Step 3.7: Create RoomTypeController**

Create `app/Modules/FrontDesk/Controllers/Api/V1/RoomTypeController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Requests\StoreRoomTypeRequest;
use App\Modules\FrontDesk\Resources\RoomTypeResource;
use App\Modules\FrontDesk\Services\RoomTypeService;
use Illuminate\Http\JsonResponse;

class RoomTypeController extends Controller
{
    public function __construct(private readonly RoomTypeService $service) {}

    public function store(StoreRoomTypeRequest $request): JsonResponse
    {
        $roomType = $this->service->createWithInventory($request->validated());

        return response()->json([
            'status'  => 1,
            'data'    => new RoomTypeResource($roomType),
            'message' => "Room type created with {$roomType->rooms->count()} rooms",
        ], 201);
    }
}
```

- [ ] **Step 3.8: Run tests**

```bash
php artisan test tests/Feature/Onboarding/RoomInventoryTest.php
```

Expected: 3 tests PASS.

- [ ] **Step 3.9: Commit**

```bash
git add app/Modules/FrontDesk/ tests/Feature/Onboarding/RoomInventoryTest.php
git commit -m "feat: add RoomType creation with auto/manual room inventory generation"
```

---

## Task 4: Onboarding Vue Pages

- [ ] **Step 4.1: Create Onboarding layout**

Create `resources/js/Layouts/OnboardingLayout.vue`:

```vue
<script setup lang="ts">
import { Head } from '@inertiajs/vue3'

defineProps<{ step?: number; totalSteps?: number }>()
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-6">
    <div class="w-full max-w-2xl">
      <!-- Progress bar -->
      <div v-if="step && totalSteps" class="mb-8">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-blue-700">Step {{ step }} of {{ totalSteps }}</span>
        </div>
        <div class="w-full bg-blue-100 rounded-full h-2">
          <div
            class="bg-blue-600 h-2 rounded-full transition-all"
            :style="{ width: `${(step / totalSteps) * 100}%` }"
          />
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-xl p-8">
        <slot />
      </div>
    </div>
  </div>
</template>
```

- [ ] **Step 4.2: Create Property Setup page (Step 1)**

Create `resources/js/Pages/Onboarding/Property/Create.vue`:

```vue
<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import OnboardingLayout from '@/Layouts/OnboardingLayout.vue'
import { useI18n } from 'vue-i18n'

defineOptions({ layout: OnboardingLayout })

const { t } = useI18n()

const form = useForm({
  name:            '',
  type:            'hotel',
  number_of_rooms: '',
  country:         '',
  city:            '',
  timezone:        '',
  currency:        '',
  check_in_time:   '14:00',
  check_out_time:  '12:00',
})

const propertyTypes = [
  { value: 'hotel',     label: 'Hotel' },
  { value: 'resort',    label: 'Resort' },
  { value: 'apartment', label: 'Apartment' },
  { value: 'villa',     label: 'Villa' },
  { value: 'hostel',    label: 'Hostel' },
]

function submit(): void {
  form.post('/api/v1/properties', {
    onSuccess: () => router.visit('/onboarding/room-types/create'),
  })
}
</script>

<template>
  <Head title="Setup Your Property" />

  <div>
    <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ t('onboarding.property.title') }}</h2>
    <p class="text-gray-500 mb-8">{{ t('onboarding.property.subtitle') }}</p>

    <form @submit.prevent="submit" class="space-y-5">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Property Name *</label>
        <AppInput v-model="form.name" :error="form.errors.name" placeholder="e.g. Grand Ocean Hotel" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Property Type *</label>
        <select v-model="form.type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
          <option v-for="pt in propertyTypes" :key="pt.value" :value="pt.value">{{ pt.label }}</option>
        </select>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Total Rooms *</label>
          <AppInput v-model="form.number_of_rooms" type="number" :error="form.errors.number_of_rooms" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
          <AppInput v-model="form.country" placeholder="BD" maxlength="2" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Check-in Time</label>
          <AppInput v-model="form.check_in_time" type="time" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Check-out Time</label>
          <AppInput v-model="form.check_out_time" type="time" />
        </div>
      </div>

      <div class="pt-4">
        <AppButton type="submit" :loading="form.processing" class="w-full">
          Continue to Room Setup →
        </AppButton>
      </div>
    </form>
  </div>
</template>
```

- [ ] **Step 4.3: Run full test suite**

```bash
composer run test
```

Expected: all tests PASS.

- [ ] **Step 4.4: Commit**

```bash
git add resources/js/Layouts/OnboardingLayout.vue \
        resources/js/Pages/Onboarding/ \
        resources/js/Stores/FrontDesk/propertyStore.ts \
        resources/js/Types/FrontDesk/
git commit -m "feat: add onboarding wizard pages (property setup, room inventory)"
```

---

## Phase 2 Completion Checklist

- [ ] `php artisan test` → all tests PASS
- [ ] `./vendor/bin/pint` → no violations
- [ ] New tenant with 0 properties → redirected to `/onboarding/property/create`
- [ ] POST `/api/v1/properties` → creates property record
- [ ] POST `/api/v1/room-types` (auto mode) → creates room type + N room records
- [ ] POST `/api/v1/room-types` (manual mode) → creates rooms with exact identifiers
- [ ] Duplicate room identifier in same property → 422 Unprocessable
- [ ] After onboarding completes → user lands on `/dashboard`
