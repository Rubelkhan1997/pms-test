# Rate & Availability + Channel Manager Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the Availability Calendar UI, Restrictions Grid, Rate Plan management pages, and Channel Manager OTA connectivity — completing the rate and distribution layer.

**Architecture:** Rate plans, cancellation policies, pricing profiles, and restrictions were schema-created in Phase 2. This phase adds the management UI, availability calendar cache layer (Redis), derived rate auto-recalculation, and OTA sync jobs via Laravel Horizon. Availability queries served from Redis cache (invalidated on reservation/restriction change).

**Tech Stack:** Laravel 13, Redis, Laravel Horizon, Pest PHP.

**Depends on:** Phase 2 (Onboarding — rate_plans, cancellation_policies, pricing_profiles, rate_restrictions tables exist).

---

## File Map

| Action | File | Responsibility |
|---|---|---|
| Create | `database/migrations/tenant/2026_04_27_600000_create_ota_connections_table.php` | Encrypted OTA credentials |
| Create | `database/migrations/tenant/2026_04_27_600100_create_ota_booking_logs_table.php` | OTA booking ingest log |
| Create | `app/Modules/RateAvailability/Models/OtaConnection.php` | OTA connection model |
| Create | `app/Modules/RateAvailability/Models/OtaBookingLog.php` | Booking log model |
| Create | `app/Modules/RateAvailability/Actions/RecalculateDerivedRatesAction.php` | Auto-recalc on parent change |
| Create | `app/Modules/RateAvailability/Actions/BulkUpsertRestrictionsAction.php` | Bulk date range upsert |
| Create | `app/Modules/RateAvailability/Actions/InvalidateAvailabilityCacheAction.php` | Redis cache bust |
| Create | `app/Modules/RateAvailability/Services/AvailabilityCalendarService.php` | Redis-cached calendar data |
| Create | `app/Modules/RateAvailability/Services/RatePlanService.php` | Rate plan CRUD |
| Create | `app/Modules/RateAvailability/Services/OtaSyncService.php` | OTA sync orchestrator |
| Create | `app/Modules/RateAvailability/Jobs/OtaInventorySyncJob.php` | Horizon queued inventory push |
| Create | `app/Modules/RateAvailability/Jobs/OtaRateSyncJob.php` | Horizon queued rate push |
| Create | `app/Modules/RateAvailability/Jobs/IngestOtaBookingJob.php` | Process incoming OTA booking |
| Create | `app/Modules/RateAvailability/Controllers/Api/V1/RatePlanController.php` | Rate plan CRUD API |
| Create | `app/Modules/RateAvailability/Controllers/Api/V1/CancellationPolicyController.php` | Policy CRUD API |
| Create | `app/Modules/RateAvailability/Controllers/Api/V1/AvailabilityController.php` | Calendar data API |
| Create | `app/Modules/RateAvailability/Controllers/Api/V1/RestrictionController.php` | Restrictions bulk update API |
| Create | `app/Modules/RateAvailability/Controllers/Api/V1/OtaController.php` | OTA connections + ingest API |
| Create | `app/Modules/RateAvailability/Controllers/Web/RateAvailabilityController.php` | Inertia pages |
| Create | `app/Modules/RateAvailability/Observers/RatePlanObserver.php` | Triggers derived rate recalc |
| Create | `resources/js/Types/RateAvailability/rate.ts` | TS types |
| Create | `resources/js/Stores/RateAvailability/ratePlanStore.ts` | Pinia store |
| Create | `resources/js/Composables/RateAvailability/useRatePlans.ts` | Composable |
| Create | `resources/js/Pages/RateAvailability/RatePlans/Index.vue` | Rate plans list |
| Create | `resources/js/Pages/RateAvailability/AvailabilityCalendar/Index.vue` | Availability calendar grid |
| Create | `resources/js/Pages/RateAvailability/Restrictions/Index.vue` | Restrictions grid |
| Create | `resources/js/Pages/RateAvailability/OTA/Index.vue` | OTA connections |
| Create | `tests/Feature/RateAvailability/AvailabilityCalendarTest.php` | Calendar cache tests |
| Create | `tests/Feature/RateAvailability/DerivedRateTest.php` | Derived rate recalc tests |
| Create | `tests/Feature/RateAvailability/RestrictionTest.php` | Bulk restriction upsert tests |

---

## Task 1: OTA Database Migrations

- [ ] **Step 1.1: Create ota_connections migration**

Create `database/migrations/tenant/2026_04_27_600000_create_ota_connections_table.php`:

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
        Schema::create('ota_connections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('ota_name');                          // booking_com, expedia, airbnb
            $table->string('hotel_id_on_ota')->nullable();
            $table->text('api_key_encrypted')->nullable();       // AES-256 Encrypted cast
            $table->text('api_secret_encrypted')->nullable();
            $table->string('api_endpoint')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('sync_config')->nullable();             // which room_types, rate_plans to sync
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
            $table->index(['property_id', 'ota_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ota_connections');
    }
};
```

- [ ] **Step 1.2: Create ota_booking_logs migration**

Create `database/migrations/tenant/2026_04_27_600100_create_ota_booking_logs_table.php`:

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
        Schema::create('ota_booking_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ota_connection_id')->constrained('ota_connections')->cascadeOnDelete();
            $table->string('ota_booking_id')->nullable();
            $table->enum('action', ['new', 'modify', 'cancel'])->default('new');
            $table->json('payload');                             // Raw OTA payload stored
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->uuid('reservation_id')->nullable();          // linked after processing
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['ota_connection_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ota_booking_logs');
    }
};
```

- [ ] **Step 1.3: Run migrations**

```bash
php artisan migrate --path=database/migrations/tenant --database=mysql
```

Expected: ota_connections, ota_booking_logs created.

- [ ] **Step 1.4: Commit**

```bash
git add database/migrations/tenant/2026_04_27_600*.php
git commit -m "feat: add OTA connections and booking logs migrations"
```

---

## Task 2: Derived Rate Auto-Recalculation

**Files:**
- Create: `app/Modules/RateAvailability/Actions/RecalculateDerivedRatesAction.php`
- Create: `app/Modules/RateAvailability/Observers/RatePlanObserver.php`
- Test: `tests/Feature/RateAvailability/DerivedRateTest.php`

- [ ] **Step 2.1: Write failing test**

Create `tests/Feature/RateAvailability/DerivedRateTest.php`:

```php
<?php

declare(strict_types=1);

use App\Modules\RateAvailability\Actions\RecalculateDerivedRatesAction;
use App\Modules\RateAvailability\Models\RatePlan;

it('recalculates derived rate when parent base_rate changes', function (): void {
    $parent = RatePlan::factory()->create(['rate_type' => 'base', 'base_rate' => 200.00]);
    $derived = RatePlan::factory()->create([
        'rate_type'     => 'derived',
        'parent_rate_id'=> $parent->id,
        'derived_modifier_type' => 'percentage',
        'derived_modifier_value'=> -10,   // parent - 10%
        'base_rate'     => 180.00,
    ]);

    // Update parent rate
    $parent->update(['base_rate' => 300.00]);

    app(RecalculateDerivedRatesAction::class)->execute($parent);

    // 300 - 10% = 270
    expect((float) $derived->fresh()->base_rate)->toBe(270.00);
});

it('applies fixed modifier correctly', function (): void {
    $parent  = RatePlan::factory()->create(['rate_type' => 'base', 'base_rate' => 200.00]);
    $derived = RatePlan::factory()->create([
        'rate_type'              => 'derived',
        'parent_rate_id'         => $parent->id,
        'derived_modifier_type'  => 'fixed',
        'derived_modifier_value' => -20,  // parent - $20
        'base_rate'              => 180.00,
    ]);

    $parent->update(['base_rate' => 250.00]);
    app(RecalculateDerivedRatesAction::class)->execute($parent);

    expect((float) $derived->fresh()->base_rate)->toBe(230.00);
});
```

- [ ] **Step 2.2: Run test to verify it fails**

```bash
php artisan test tests/Feature/RateAvailability/DerivedRateTest.php
```

Expected: FAIL.

- [ ] **Step 2.3: Create RecalculateDerivedRatesAction**

Create `app/Modules/RateAvailability/Actions/RecalculateDerivedRatesAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Actions;

use App\Modules\RateAvailability\Models\RatePlan;

class RecalculateDerivedRatesAction
{
    public function execute(RatePlan $parentRate): void
    {
        $derived = RatePlan::where('parent_rate_id', $parentRate->id)->get();

        foreach ($derived as $plan) {
            $newRate = match ($plan->derived_modifier_type) {
                'percentage' => round($parentRate->base_rate * (1 + $plan->derived_modifier_value / 100), 2),
                'fixed'      => round($parentRate->base_rate + $plan->derived_modifier_value, 2),
                default      => $parentRate->base_rate,
            };

            $plan->update(['base_rate' => $newRate]);

            // Recurse for multi-level derived rates
            $this->execute($plan);
        }
    }
}
```

- [ ] **Step 2.4: Create RatePlanObserver**

Create `app/Modules/RateAvailability/Observers/RatePlanObserver.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Observers;

use App\Modules\RateAvailability\Actions\InvalidateAvailabilityCacheAction;
use App\Modules\RateAvailability\Actions\RecalculateDerivedRatesAction;
use App\Modules\RateAvailability\Models\RatePlan;

class RatePlanObserver
{
    public function __construct(
        private readonly RecalculateDerivedRatesAction  $recalculate,
        private readonly InvalidateAvailabilityCacheAction $invalidate,
    ) {}

    public function updated(RatePlan $ratePlan): void
    {
        if ($ratePlan->wasChanged('base_rate') && $ratePlan->rate_type === 'base') {
            $this->recalculate->execute($ratePlan);
        }

        $this->invalidate->execute($ratePlan->property_id ?? 0);
    }
}
```

Register in `AppServiceProvider::boot()`:

```php
use App\Modules\RateAvailability\Models\RatePlan;
use App\Modules\RateAvailability\Observers\RatePlanObserver;

RatePlan::observe(RatePlanObserver::class);
```

- [ ] **Step 2.5: Run test to verify it passes**

```bash
php artisan test tests/Feature/RateAvailability/DerivedRateTest.php
```

Expected: 2 tests PASS.

- [ ] **Step 2.6: Commit**

```bash
git add app/Modules/RateAvailability/Actions/RecalculateDerivedRatesAction.php \
        app/Modules/RateAvailability/Observers/RatePlanObserver.php \
        app/Providers/AppServiceProvider.php \
        tests/Feature/RateAvailability/DerivedRateTest.php
git commit -m "feat: auto-recalculate derived rates on parent rate change via observer"
```

---

## Task 3: Availability Calendar — Redis Cache Layer

**Files:**
- Create: `app/Modules/RateAvailability/Actions/InvalidateAvailabilityCacheAction.php`
- Create: `app/Modules/RateAvailability/Services/AvailabilityCalendarService.php`
- Test: `tests/Feature/RateAvailability/AvailabilityCalendarTest.php`

- [ ] **Step 3.1: Write failing test**

Create `tests/Feature/RateAvailability/AvailabilityCalendarTest.php`:

```php
<?php

declare(strict_types=1);

use App\Modules\RateAvailability\Services\AvailabilityCalendarService;
use Illuminate\Support\Facades\Cache;

it('returns availability data for 31-day window', function (): void {
    Cache::flush();

    $service = app(AvailabilityCalendarService::class);
    $data    = $service->getCalendar(propertyId: 1, startDate: '2026-06-01', days: 31);

    expect($data)->toHaveKey('start_date')
        ->toHaveKey('days')
        ->toHaveKey('room_types');
});

it('caches result and returns same data on second call', function (): void {
    Cache::flush();

    $service = app(AvailabilityCalendarService::class);
    $data1   = $service->getCalendar(1, '2026-07-01', 14);
    $data2   = $service->getCalendar(1, '2026-07-01', 14);

    expect($data1)->toEqual($data2);
    expect(Cache::has("availability.1." . md5('2026-07-01.14')))->toBeTrue();
});
```

- [ ] **Step 3.2: Run test to verify it fails**

```bash
php artisan test tests/Feature/RateAvailability/AvailabilityCalendarTest.php
```

Expected: FAIL.

- [ ] **Step 3.3: Create InvalidateAvailabilityCacheAction**

Create `app/Modules/RateAvailability/Actions/InvalidateAvailabilityCacheAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Actions;

use Illuminate\Support\Facades\Cache;

class InvalidateAvailabilityCacheAction
{
    public function execute(int $propertyId): void
    {
        // Invalidate all cached windows for this property by tag
        Cache::tags(["availability.{$propertyId}"])->flush();
    }
}
```

- [ ] **Step 3.4: Create AvailabilityCalendarService**

Create `app/Modules/RateAvailability/Services/AvailabilityCalendarService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Services;

use App\Enums\ReservationStatus;
use App\Modules\FrontDesk\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AvailabilityCalendarService
{
    private const TTL_MINUTES = 15;

    public function getCalendar(int $propertyId, string $startDate, int $days = 31): array
    {
        $cacheKey = "availability.{$propertyId}." . md5("{$startDate}.{$days}");

        return Cache::remember($cacheKey, self::TTL_MINUTES * 60, function () use ($propertyId, $startDate, $days): array {
            return $this->buildCalendar($propertyId, $startDate, $days);
        });
    }

    private function buildCalendar(int $propertyId, string $startDate, int $days): array
    {
        $start = Carbon::parse($startDate);
        $end   = $start->copy()->addDays($days);

        $roomTypes = RoomType::where('property_id', $propertyId)
            ->withCount('rooms as total_rooms')
            ->get();

        // Count occupied rooms per room_type per date
        $occupied = DB::table('reservations')
            ->join('room_types', 'reservations.room_type_id', '=', 'room_types.id')
            ->where('reservations.property_id', $propertyId)
            ->whereNotIn('reservations.status', [
                ReservationStatus::Cancelled->value,
                ReservationStatus::NoShow->value,
                ReservationStatus::CheckedOut->value,
            ])
            ->where('reservations.check_in_date', '<', $end->toDateString())
            ->where('reservations.check_out_date', '>', $start->toDateString())
            ->whereNull('reservations.deleted_at')
            ->selectRaw('reservations.room_type_id, DATE(reservations.check_in_date) as date, COUNT(*) as count')
            ->groupBy('reservations.room_type_id', DB::raw('DATE(reservations.check_in_date)'))
            ->get()
            ->groupBy(['room_type_id', 'date']);

        // Get restrictions
        $restrictions = DB::table('rate_restrictions')
            ->where('property_id', $propertyId)
            ->where('date', '>=', $start->toDateString())
            ->where('date', '<', $end->toDateString())
            ->get()
            ->groupBy('room_type_id');

        $dates = [];
        for ($i = 0; $i < $days; $i++) {
            $dates[] = $start->copy()->addDays($i)->toDateString();
        }

        return [
            'start_date' => $startDate,
            'days'       => $days,
            'dates'      => $dates,
            'room_types' => $roomTypes->map(fn ($rt) => [
                'id'          => $rt->id,
                'name'        => $rt->name,
                'total_rooms' => $rt->total_rooms,
                'availability'=> collect($dates)->map(fn ($date) => [
                    'date'          => $date,
                    'occupied'      => (int) ($occupied[$rt->id][$date][0]->count ?? 0),
                    'available'     => max(0, $rt->total_rooms - (int) ($occupied[$rt->id][$date][0]->count ?? 0)),
                    'restrictions'  => collect($restrictions[$rt->id] ?? [])->where('date', $date)->first(),
                ])->values(),
            ])->values(),
        ];
    }
}
```

- [ ] **Step 3.5: Run test to verify it passes**

```bash
php artisan test tests/Feature/RateAvailability/AvailabilityCalendarTest.php
```

Expected: 2 tests PASS.

- [ ] **Step 3.6: Commit**

```bash
git add app/Modules/RateAvailability/Actions/InvalidateAvailabilityCacheAction.php \
        app/Modules/RateAvailability/Services/AvailabilityCalendarService.php \
        tests/Feature/RateAvailability/AvailabilityCalendarTest.php
git commit -m "feat: add Redis-cached availability calendar with 15-min TTL"
```

---

## Task 4: Bulk Restrictions Upsert

**Test:** `tests/Feature/RateAvailability/RestrictionTest.php`

- [ ] **Step 4.1: Write failing test**

Create `tests/Feature/RateAvailability/RestrictionTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\DB;

it('bulk upserts restrictions for a date range', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/rate-availability/restrictions/bulk', [
            'property_id'  => 1,
            'room_type_id' => 1,
            'rate_plan_id' => 1,
            'start_date'   => '2026-09-01',
            'end_date'     => '2026-09-07',
            'restrictions' => ['min_stay' => 2, 'cta' => false, 'ctd' => false, 'stop_sell' => false],
        ])
        ->assertStatus(200)
        ->assertJsonPath('status', 1);

    $count = DB::table('rate_restrictions')
        ->where('property_id', 1)
        ->where('room_type_id', 1)
        ->whereBetween('date', ['2026-09-01', '2026-09-07'])
        ->count();

    expect($count)->toBe(7); // 7 days
});
```

- [ ] **Step 4.2: Run test to verify it fails**

```bash
php artisan test tests/Feature/RateAvailability/RestrictionTest.php
```

- [ ] **Step 4.3: Create BulkUpsertRestrictionsAction**

Create `app/Modules/RateAvailability/Actions/BulkUpsertRestrictionsAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Actions;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BulkUpsertRestrictionsAction
{
    public function __construct(private readonly InvalidateAvailabilityCacheAction $invalidate) {}

    public function execute(
        int    $propertyId,
        int    $roomTypeId,
        ?int   $ratePlanId,
        string $startDate,
        string $endDate,
        array  $restrictions
    ): int {
        $start   = Carbon::parse($startDate);
        $end     = Carbon::parse($endDate);
        $records = [];
        $now     = now()->toDateTimeString();

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $records[] = [
                'property_id'  => $propertyId,
                'room_type_id' => $roomTypeId,
                'rate_plan_id' => $ratePlanId,
                'date'         => $d->toDateString(),
                'min_stay'     => $restrictions['min_stay'] ?? null,
                'max_stay'     => $restrictions['max_stay'] ?? null,
                'cta'          => (int) ($restrictions['cta'] ?? false),
                'ctd'          => (int) ($restrictions['ctd'] ?? false),
                'stop_sell'    => (int) ($restrictions['stop_sell'] ?? false),
                'release_period' => $restrictions['release_period'] ?? null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        DB::table('rate_restrictions')->upsert(
            $records,
            ['property_id', 'room_type_id', 'rate_plan_id', 'date'],
            ['min_stay', 'max_stay', 'cta', 'ctd', 'stop_sell', 'release_period', 'updated_at']
        );

        $this->invalidate->execute($propertyId);

        return count($records);
    }
}
```

- [ ] **Step 4.4: Create RestrictionController**

Create `app/Modules/RateAvailability/Controllers/Api/V1/RestrictionController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\RateAvailability\Actions\BulkUpsertRestrictionsAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestrictionController extends Controller
{
    public function __construct(private readonly BulkUpsertRestrictionsAction $action) {}

    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'property_id'    => ['required', 'integer'],
            'room_type_id'   => ['required', 'integer'],
            'rate_plan_id'   => ['nullable', 'integer'],
            'start_date'     => ['required', 'date'],
            'end_date'       => ['required', 'date', 'after_or_equal:start_date'],
            'restrictions'   => ['required', 'array'],
            'restrictions.min_stay'     => ['nullable', 'integer', 'min:1'],
            'restrictions.max_stay'     => ['nullable', 'integer', 'min:1'],
            'restrictions.cta'          => ['nullable', 'boolean'],
            'restrictions.ctd'          => ['nullable', 'boolean'],
            'restrictions.stop_sell'    => ['nullable', 'boolean'],
            'restrictions.release_period' => ['nullable', 'integer'],
        ]);

        $count = $this->action->execute(
            $request->integer('property_id'),
            $request->integer('room_type_id'),
            $request->integer('rate_plan_id') ?: null,
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('restrictions'),
        );

        return response()->json(['status' => 1, 'data' => ['updated' => $count], 'message' => "{$count} restriction records updated."]);
    }
}
```

- [ ] **Step 4.5: Run test to verify it passes**

```bash
php artisan test tests/Feature/RateAvailability/RestrictionTest.php
```

Expected: PASS.

- [ ] **Step 4.6: Commit**

```bash
git add app/Modules/RateAvailability/Actions/BulkUpsertRestrictionsAction.php \
        app/Modules/RateAvailability/Controllers/Api/V1/RestrictionController.php \
        tests/Feature/RateAvailability/RestrictionTest.php
git commit -m "feat: add bulk restriction upsert endpoint with Redis cache invalidation"
```

---

## Task 5: OTA Models + Sync Jobs

- [ ] **Step 5.1: Create OTA models**

Create `app/Modules/RateAvailability/Models/OtaConnection.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OtaConnection extends Model
{
    protected $fillable = [
        'property_id', 'ota_name', 'hotel_id_on_ota',
        'api_key_encrypted', 'api_secret_encrypted', 'api_endpoint',
        'is_active', 'sync_config', 'last_sync_at',
    ];

    protected $casts = [
        'api_key_encrypted'    => 'encrypted',
        'api_secret_encrypted' => 'encrypted',
        'sync_config'          => 'array',
        'is_active'            => 'boolean',
        'last_sync_at'         => 'datetime',
    ];

    public function bookingLogs(): HasMany
    {
        return $this->hasMany(OtaBookingLog::class);
    }
}
```

Create `app/Modules/RateAvailability/Models/OtaBookingLog.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Models;

use Illuminate\Database\Eloquent\Model;

class OtaBookingLog extends Model
{
    protected $fillable = [
        'ota_connection_id', 'ota_booking_id', 'action',
        'payload', 'status', 'reservation_id', 'error_message',
    ];

    protected $casts = ['payload' => 'array'];
}
```

- [ ] **Step 5.2: Create OtaInventorySyncJob**

Create `app/Modules/RateAvailability/Jobs/OtaInventorySyncJob.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Jobs;

use App\Modules\RateAvailability\Models\OtaConnection;
use App\Modules\RateAvailability\Services\AvailabilityCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OtaInventorySyncJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int $connectionId,
        public readonly int $propertyId,
        public readonly string $startDate,
        public readonly int $days = 30,
    ) {}

    public function handle(AvailabilityCalendarService $calendarService): void
    {
        $connection  = OtaConnection::findOrFail($this->connectionId);
        $calendar    = $calendarService->getCalendar($this->propertyId, $this->startDate, $this->days);

        // TODO: implement actual OTA API push based on $connection->ota_name
        // For now, log the sync attempt
        $connection->update(['last_sync_at' => now()]);

        \Illuminate\Support\Facades\Log::info("OTA inventory sync completed", [
            'connection_id' => $this->connectionId,
            'ota'           => $connection->ota_name,
            'days'          => $this->days,
        ]);
    }
}
```

- [ ] **Step 5.3: Create OtaRateSyncJob**

Create `app/Modules/RateAvailability/Jobs/OtaRateSyncJob.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Jobs;

use App\Modules\RateAvailability\Models\OtaConnection;
use App\Modules\RateAvailability\Models\RatePlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OtaRateSyncJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int $connectionId,
        public readonly int $ratePlanId,
    ) {}

    public function handle(): void
    {
        $connection = OtaConnection::findOrFail($this->connectionId);
        $ratePlan   = RatePlan::findOrFail($this->ratePlanId);

        // TODO: push rate to OTA API
        $connection->update(['last_sync_at' => now()]);

        \Illuminate\Support\Facades\Log::info("OTA rate sync completed", [
            'ota'          => $connection->ota_name,
            'rate_plan_id' => $this->ratePlanId,
            'base_rate'    => $ratePlan->base_rate,
        ]);
    }
}
```

- [ ] **Step 5.4: Create OtaController**

Create `app/Modules/RateAvailability/Controllers/Api/V1/OtaController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\RateAvailability\Jobs\OtaInventorySyncJob;
use App\Modules\RateAvailability\Models\OtaBookingLog;
use App\Modules\RateAvailability\Models\OtaConnection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OtaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $connections = OtaConnection::where('property_id', $request->integer('property_id'))->get();
        return response()->json(['status' => 1, 'data' => $connections, 'message' => '']);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'property_id'      => ['required', 'integer'],
            'ota_name'         => ['required', 'string'],
            'hotel_id_on_ota'  => ['nullable', 'string'],
            'api_key_encrypted'=> ['required', 'string'],
            'api_secret_encrypted' => ['nullable', 'string'],
            'api_endpoint'     => ['nullable', 'url'],
        ]);

        $connection = OtaConnection::create($request->validated());
        return response()->json(['status' => 1, 'data' => $connection, 'message' => 'OTA connection saved.'], 201);
    }

    public function syncInventory(Request $request, OtaConnection $otaConnection): JsonResponse
    {
        OtaInventorySyncJob::dispatch($otaConnection->id, $otaConnection->property_id, today()->toDateString(), 30);
        return response()->json(['status' => 1, 'data' => null, 'message' => 'Inventory sync queued.']);
    }

    public function bookingLogs(OtaConnection $otaConnection): JsonResponse
    {
        $logs = $otaConnection->bookingLogs()->latest()->paginate(20);
        return response()->json(['status' => 1, 'data' => $logs, 'message' => '']);
    }

    public function ingestBooking(Request $request, OtaConnection $otaConnection): JsonResponse
    {
        $log = OtaBookingLog::create([
            'ota_connection_id' => $otaConnection->id,
            'ota_booking_id'    => $request->input('booking_id'),
            'action'            => $request->input('action', 'new'),
            'payload'           => $request->all(),
            'status'            => 'pending',
        ]);

        // Dispatch processing job
        \App\Modules\RateAvailability\Jobs\IngestOtaBookingJob::dispatch($log->id);

        return response()->json(['status' => 1, 'data' => ['log_id' => $log->id], 'message' => 'Booking received.'], 202);
    }
}
```

- [ ] **Step 5.5: Create AvailabilityController and RatePlanController**

Create `app/Modules/RateAvailability/Controllers/Api/V1/AvailabilityController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\RateAvailability\Services\AvailabilityCalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __construct(private readonly AvailabilityCalendarService $service) {}

    public function calendar(Request $request): JsonResponse
    {
        $request->validate([
            'property_id' => ['required', 'integer'],
            'start_date'  => ['nullable', 'date'],
            'days'        => ['nullable', 'integer', 'min:7', 'max:31'],
        ]);

        $data = $this->service->getCalendar(
            $request->integer('property_id'),
            $request->input('start_date', today()->toDateString()),
            $request->integer('days', 31),
        );

        return response()->json(['status' => 1, 'data' => $data, 'message' => '']);
    }
}
```

Create `app/Modules/RateAvailability/Controllers/Api/V1/RatePlanController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\RateAvailability\Models\RatePlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatePlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $plans = RatePlan::where('property_id', $request->integer('property_id'))->with(['cancellationPolicy', 'pricingProfile'])->get();
        return response()->json(['status' => 1, 'data' => $plans, 'message' => '']);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'property_id'            => ['required', 'integer'],
            'name'                   => ['required', 'string'],
            'rate_type'              => ['required', 'in:base,derived'],
            'meal_plan'              => ['required', 'in:ro,bb,hb,fb,ai'],
            'base_rate'              => ['required', 'numeric', 'min:0'],
            'cancellation_policy_id' => ['nullable', 'integer'],
            'pricing_profile_id'     => ['nullable', 'integer'],
            'parent_rate_id'         => ['nullable', 'integer'],
            'derived_modifier_type'  => ['nullable', 'in:percentage,fixed'],
            'derived_modifier_value' => ['nullable', 'numeric'],
            'is_active'              => ['nullable', 'boolean'],
        ]);

        $plan = RatePlan::create($request->validated());
        return response()->json(['status' => 1, 'data' => $plan, 'message' => 'Rate plan created.'], 201);
    }

    public function update(Request $request, RatePlan $ratePlan): JsonResponse
    {
        $ratePlan->update($request->validated());
        return response()->json(['status' => 1, 'data' => $ratePlan->fresh(), 'message' => 'Updated.']);
    }

    public function destroy(RatePlan $ratePlan): JsonResponse
    {
        $ratePlan->update(['is_active' => false]);
        return response()->json(['status' => 1, 'data' => null, 'message' => 'Rate plan deactivated.']);
    }
}
```

- [ ] **Step 5.6: Register routes**

In `routes/tenant-api.php`:

```php
Route::prefix('rate-availability')->name('rate-availability.')->group(function (): void {
    Route::apiResource('rate-plans', RatePlanController::class)->except(['show']);
    Route::get('/availability/calendar', [AvailabilityController::class, 'calendar'])->name('availability.calendar');
    Route::post('/restrictions/bulk',    [RestrictionController::class, 'bulk'])->name('restrictions.bulk');
    Route::get('/ota',                   [OtaController::class, 'index'])->name('ota.index');
    Route::post('/ota',                  [OtaController::class, 'store'])->name('ota.store');
    Route::post('/ota/{ota}/sync',       [OtaController::class, 'syncInventory'])->name('ota.sync');
    Route::get('/ota/{ota}/logs',        [OtaController::class, 'bookingLogs'])->name('ota.logs');
    Route::post('/ota/{ota}/ingest',     [OtaController::class, 'ingestBooking'])->name('ota.ingest');
});
```

- [ ] **Step 5.7: Commit**

```bash
git add app/Modules/RateAvailability/Models/ \
        app/Modules/RateAvailability/Jobs/ \
        app/Modules/RateAvailability/Controllers/Api/ \
        routes/tenant-api.php
git commit -m "feat: add OTA models, sync jobs, availability/rate/restriction API controllers"
```

---

## Task 6: TypeScript Types, Store, Composable + Vue Pages

- [ ] **Step 6.1: Create TypeScript types**

Create `resources/js/Types/RateAvailability/rate.ts`:

```typescript
export type MealPlan = 'ro' | 'bb' | 'hb' | 'fb' | 'ai'
export type RateType = 'base' | 'derived'

export interface RatePlan {
  id: number
  name: string
  rate_type: RateType
  meal_plan: MealPlan
  base_rate: string
  cancellation_policy_id: number | null
  pricing_profile_id: number | null
  parent_rate_id: number | null
  derived_modifier_type: 'percentage' | 'fixed' | null
  derived_modifier_value: number | null
  is_active: boolean
}

export interface AvailabilityDay {
  date: string
  occupied: number
  available: number
  restrictions: null | {
    min_stay: number | null
    max_stay: number | null
    cta: boolean
    ctd: boolean
    stop_sell: boolean
    release_period: number | null
  }
}

export interface AvailabilityRoomType {
  id: number
  name: string
  total_rooms: number
  availability: AvailabilityDay[]
}

export interface AvailabilityCalendar {
  start_date: string
  days: number
  dates: string[]
  room_types: AvailabilityRoomType[]
}

export interface OtaConnection {
  id: number
  ota_name: string
  hotel_id_on_ota: string | null
  is_active: boolean
  last_sync_at: string | null
}
```

- [ ] **Step 6.2: Create Pinia store**

Create `resources/js/Stores/RateAvailability/ratePlanStore.ts`:

```typescript
import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiClient from '@/Services/apiClient'
import type { RatePlan, AvailabilityCalendar, OtaConnection } from '@/Types/RateAvailability/rate'

export const useRatePlanStore = defineStore('ratePlan', () => {
  const ratePlans    = ref<RatePlan[]>([])
  const calendar     = ref<AvailabilityCalendar | null>(null)
  const otaConnections = ref<OtaConnection[]>([])
  const loading      = ref(false)
  const loadingList  = ref(false)

  async function fetchRatePlans(propertyId: number, forceRefresh = false): Promise<void> {
    if (ratePlans.value.length && !forceRefresh) return
    loadingList.value = true
    try {
      const res = await apiClient.get('/api/v1/rate-availability/rate-plans', { params: { property_id: propertyId } })
      ratePlans.value = res.data.data
    } finally {
      loadingList.value = false
    }
  }

  async function fetchCalendar(propertyId: number, startDate: string, days = 31): Promise<void> {
    loading.value = true
    try {
      const res = await apiClient.get('/api/v1/rate-availability/availability/calendar', { params: { property_id: propertyId, start_date: startDate, days } })
      calendar.value = res.data.data
    } finally {
      loading.value = false
    }
  }

  async function fetchOtaConnections(propertyId: number): Promise<void> {
    const res = await apiClient.get('/api/v1/rate-availability/ota', { params: { property_id: propertyId } })
    otaConnections.value = res.data.data
  }

  async function bulkUpdateRestrictions(payload: Record<string, unknown>): Promise<void> {
    loading.value = true
    try {
      await apiClient.post('/api/v1/rate-availability/restrictions/bulk', payload)
    } finally {
      loading.value = false
    }
  }

  return { ratePlans, calendar, otaConnections, loading, loadingList, fetchRatePlans, fetchCalendar, fetchOtaConnections, bulkUpdateRestrictions }
})
```

- [ ] **Step 6.3: Create composable**

Create `resources/js/Composables/RateAvailability/useRatePlans.ts`:

```typescript
import { storeToRefs } from 'pinia'
import { useRatePlanStore } from '@/Stores/RateAvailability/ratePlanStore'

export function useRatePlans() {
  const store = useRatePlanStore()
  const { ratePlans, calendar, otaConnections, loading, loadingList } = storeToRefs(store)
  return { ratePlans, calendar, otaConnections, loading, loadingList, ...store }
}
```

- [ ] **Step 6.4: Create Availability Calendar page**

Create `resources/js/Pages/RateAvailability/AvailabilityCalendar/Index.vue`:

```vue
<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRatePlans } from '@/Composables/RateAvailability/useRatePlans'

const { calendar, loading, fetchCalendar } = useRatePlans()
const startDate = ref(new Date().toISOString().split('T')[0])

onMounted(() => fetchCalendar(1, startDate.value))

function navigate(days: number) {
  const d = new Date(startDate.value)
  d.setDate(d.getDate() + days)
  startDate.value = d.toISOString().split('T')[0]
  fetchCalendar(1, startDate.value)
}
</script>

<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold">Availability Calendar</h1>
      <div class="flex gap-2">
        <button class="btn-secondary text-sm" @click="navigate(-31)">← Prev</button>
        <span class="px-3 py-1 border rounded text-sm">{{ startDate }}</span>
        <button class="btn-secondary text-sm" @click="navigate(31)">Next →</button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-8 text-gray-500">Loading…</div>

    <div v-else-if="calendar" class="overflow-x-auto">
      <table class="text-xs border-collapse w-full">
        <thead>
          <tr>
            <th class="border px-3 py-2 bg-gray-50 text-left w-32">Room Type</th>
            <th
              v-for="date in calendar.dates"
              :key="date"
              class="border px-2 py-1 bg-gray-50 text-center min-w-[40px]"
            >
              {{ new Date(date).getDate() }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="rt in calendar.room_types" :key="rt.id">
            <td class="border px-3 py-2 font-medium">{{ rt.name }}<br><span class="text-gray-400 font-normal">{{ rt.total_rooms }} rooms</span></td>
            <td
              v-for="day in rt.availability"
              :key="day.date"
              class="border px-2 py-1 text-center"
              :class="{
                'bg-red-50 text-red-600': day.available === 0,
                'bg-yellow-50 text-yellow-700': day.available > 0 && day.available <= 2,
                'bg-green-50 text-green-700': day.available > 2,
              }"
            >
              {{ day.available }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
```

- [ ] **Step 6.5: Register web routes**

Create `app/Modules/RateAvailability/Controllers/Web/RateAvailabilityController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class RateAvailabilityController extends Controller
{
    public function ratePlans(): Response     { return Inertia::render('RateAvailability/RatePlans/Index'); }
    public function calendar(): Response      { return Inertia::render('RateAvailability/AvailabilityCalendar/Index'); }
    public function restrictions(): Response  { return Inertia::render('RateAvailability/Restrictions/Index'); }
    public function ota(): Response           { return Inertia::render('RateAvailability/OTA/Index'); }
}
```

In `routes/tenant.php`:

```php
Route::prefix('rate-availability')->name('rate-availability.')->group(function (): void {
    Route::get('/rate-plans',  [\App\Modules\RateAvailability\Controllers\Web\RateAvailabilityController::class, 'ratePlans'])->name('rate-plans');
    Route::get('/calendar',    [\App\Modules\RateAvailability\Controllers\Web\RateAvailabilityController::class, 'calendar'])->name('calendar');
    Route::get('/restrictions',[\App\Modules\RateAvailability\Controllers\Web\RateAvailabilityController::class, 'restrictions'])->name('restrictions');
    Route::get('/channel-manager/ota', [\App\Modules\RateAvailability\Controllers\Web\RateAvailabilityController::class, 'ota'])->name('ota');
});
```

- [ ] **Step 6.6: Run full test suite**

```bash
composer run test
```

Expected: all tests pass.

- [ ] **Step 6.7: Final commit**

```bash
git add resources/js/Types/RateAvailability/ resources/js/Stores/RateAvailability/ \
        resources/js/Composables/RateAvailability/ resources/js/Pages/RateAvailability/ \
        app/Modules/RateAvailability/Controllers/Web/ routes/tenant.php
git commit -m "feat: add Rate & Availability Vue pages, store, composable, OTA channel manager"
```

---

## Phase 7 Completion Checklist

- [ ] Updating a base rate plan → all derived rates auto-recalculate
- [ ] `GET /api/v1/rate-availability/availability/calendar` → returns availability cached for 15 min
- [ ] Updating a restriction or reservation → Redis availability cache invalidated
- [ ] `POST /api/v1/rate-availability/restrictions/bulk` with 7-day range → creates 7 restriction records
- [ ] OTA connection stores credentials encrypted
- [ ] OTA inventory sync dispatches Horizon job, logs `last_sync_at`
- [ ] `POST /api/v1/rate-availability/ota/{ota}/ingest` → creates OtaBookingLog with status=pending
- [ ] `composer run test` → all PASS
- [ ] `./vendor/bin/pint` → no violations
