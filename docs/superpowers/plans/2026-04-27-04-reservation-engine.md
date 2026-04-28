# Front Desk & Reservation Engine Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the core reservation booking engine — ACID-safe, double-booking-proof hub that drives all room inventory, guest identity, pricing logic, and folio creation.

**Architecture:** UUID primary keys. Row-level DB locking (`SELECT FOR UPDATE`) prevents concurrent double-bookings. Policy snapshot captured as JSON at booking time. Folio created atomically in the same transaction. Full audit log on every state change. Status machine enforced via service layer. Real-time Tape Chart updates via Reverb WebSocket.

**Tech Stack:** Laravel 13, MySQL 8 (row-level locks), Reverb WebSocket, Pest PHP.

**Depends on:** Phase 2 (Onboarding Wizard — properties, room_types, rooms, rate_plans tables must exist).

---

## File Map

| Action | File | Responsibility |
|---|---|---|
| Create | `database/migrations/tenant/2026_04_27_200000_create_reservations_table.php` | Core reservations schema |
| Create | `database/migrations/tenant/2026_04_27_200100_create_reservation_guests_table.php` | Multi-guest per booking |
| Create | `database/migrations/tenant/2026_04_27_200200_create_reservation_audit_log_table.php` | Immutable state change log |
| Create | `database/migrations/tenant/2026_04_27_200300_create_folios_table.php` | Financial container per reservation |
| Create | `database/migrations/tenant/2026_04_27_200400_create_room_blocks_table.php` | OOO/OOS maintenance blocks |
| Create | `app/Enums/ReservationStatus.php` | Status machine enum |
| Create | `app/Enums/FolioStatus.php` | Folio status enum |
| Create | `app/Modules/FrontDesk/Models/Reservation.php` | Reservation eloquent model |
| Create | `app/Modules/FrontDesk/Models/ReservationGuest.php` | Pivot guest model |
| Create | `app/Modules/FrontDesk/Models/ReservationAuditLog.php` | Audit log model |
| Create | `app/Modules/FrontDesk/Models/Folio.php` | Folio model |
| Create | `app/Modules/FrontDesk/Models/RoomBlock.php` | Room block model |
| Create | `app/Modules/FrontDesk/Actions/CheckAvailabilityAction.php` | SELECT FOR UPDATE availability |
| Create | `app/Modules/FrontDesk/Actions/CalculatePricingAction.php` | OBP nightly price calc |
| Create | `app/Modules/FrontDesk/Actions/CapturePolicySnapshotAction.php` | JSON snapshot at booking |
| Create | `app/Modules/FrontDesk/Actions/InitializeFolioAction.php` | Atomic folio creation |
| Create | `app/Modules/FrontDesk/Actions/CreateReservationAction.php` | Full transactional booking commit |
| Create | `app/Modules/FrontDesk/Actions/CheckInAction.php` | Check-in state transition |
| Create | `app/Modules/FrontDesk/Actions/CheckOutAction.php` | Check-out state transition |
| Create | `app/Modules/FrontDesk/Actions/CancelReservationAction.php` | Cancel with penalty calc |
| Create | `app/Modules/FrontDesk/Actions/MarkNoShowAction.php` | No-show with fee posting |
| Create | `app/Modules/FrontDesk/Services/ReservationService.php` | Business logic orchestrator |
| Create | `app/Modules/FrontDesk/Services/TapeChartService.php` | Tape chart data queries |
| Create | `app/Modules/FrontDesk/Controllers/Api/V1/ReservationController.php` | CRUD + status actions API |
| Create | `app/Modules/FrontDesk/Controllers/Api/V1/TapeChartController.php` | Tape chart data API |
| Create | `app/Modules/FrontDesk/Controllers/Api/V1/RoomBlockController.php` | Room block API |
| Create | `app/Modules/FrontDesk/Controllers/Web/ReservationController.php` | Inertia pages |
| Create | `app/Modules/FrontDesk/Data/ReservationData.php` | Reservation DTO |
| Create | `app/Modules/FrontDesk/Requests/StoreReservationRequest.php` | Create validation |
| Create | `app/Modules/FrontDesk/Requests/UpdateReservationRequest.php` | Update validation |
| Create | `app/Modules/FrontDesk/Resources/ReservationResource.php` | API resource |
| Create | `app/Modules/FrontDesk/Observers/ReservationObserver.php` | Fires HK dirty on checkout |
| Create | `app/Events/ReservationStatusChanged.php` | Reverb broadcast event |
| Create | `resources/js/Pages/FrontDesk/Reservation/Index.vue` | List + Tape Chart |
| Create | `resources/js/Pages/FrontDesk/Reservation/Create.vue` | New booking form |
| Create | `resources/js/Pages/FrontDesk/Reservation/Show.vue` | Booking detail + actions |
| Create | `resources/js/Pages/FrontDesk/Arrivals/Index.vue` | Today's arrivals |
| Create | `resources/js/Pages/FrontDesk/InHouse/Index.vue` | In-house guests |
| Create | `resources/js/Pages/FrontDesk/Departures/Index.vue` | Today's departures |
| Create | `resources/js/Pages/FrontDesk/RoomBlocks/Index.vue` | Room blocks management |
| Create | `resources/js/Stores/FrontDesk/reservationStore.ts` | Reservation state |
| Create | `resources/js/Composables/FrontDesk/useReservations.ts` | Reservation composable |
| Create | `resources/js/Types/FrontDesk/reservation.ts` | TS types |
| Create | `resources/js/Utils/Mappers/reservation.ts` | API ↔ TS mapper |
| Create | `tests/Feature/FrontDesk/ReservationMigrationTest.php` | Schema tests |
| Create | `tests/Feature/FrontDesk/AvailabilityTest.php` | Double-booking prevention |
| Create | `tests/Feature/FrontDesk/PricingEngineTest.php` | OBP calculation tests |
| Create | `tests/Feature/FrontDesk/CreateReservationTest.php` | Full booking commit test |
| Create | `tests/Feature/FrontDesk/ReservationStatusTest.php` | Status machine tests |
| Create | `tests/Feature/FrontDesk/RoomBlockTest.php` | Room block tests |

---

## Task 1: Database Migrations

**Files:**
- Create: `database/migrations/tenant/2026_04_27_200000_create_reservations_table.php`
- Create: `database/migrations/tenant/2026_04_27_200100_create_reservation_guests_table.php`
- Create: `database/migrations/tenant/2026_04_27_200200_create_reservation_audit_log_table.php`
- Create: `database/migrations/tenant/2026_04_27_200300_create_folios_table.php`
- Create: `database/migrations/tenant/2026_04_27_200400_create_room_blocks_table.php`
- Create: `app/Enums/ReservationStatus.php`
- Create: `app/Enums/FolioStatus.php`

- [ ] **Step 1.1: Create ReservationStatus enum**

Create `app/Enums/ReservationStatus.php`:

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum ReservationStatus: string
{
    case Confirmed            = 'confirmed';
    case WaitingConfirmation  = 'waiting_confirmation';
    case CheckedIn            = 'checked_in';
    case CheckedOut           = 'checked_out';
    case Cancelled            = 'cancelled';
    case NoShow               = 'no_show';

    public function label(): string
    {
        return match($this) {
            self::Confirmed           => 'Confirmed',
            self::WaitingConfirmation => 'Waiting Confirmation',
            self::CheckedIn           => 'Checked In',
            self::CheckedOut          => 'Checked Out',
            self::Cancelled           => 'Cancelled',
            self::NoShow              => 'No Show',
        };
    }

    public function canCheckIn(): bool
    {
        return $this === self::Confirmed || $this === self::WaitingConfirmation;
    }

    public function canCheckOut(): bool
    {
        return $this === self::CheckedIn;
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::Confirmed, self::WaitingConfirmation], true);
    }
}
```

- [ ] **Step 1.2: Create FolioStatus enum**

Create `app/Enums/FolioStatus.php`:

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum FolioStatus: string
{
    case Pending  = 'pending';
    case Active   = 'active';
    case Settled  = 'settled';
    case Voided   = 'voided';
}
```

- [ ] **Step 1.3: Create reservations migration**

Create `database/migrations/tenant/2026_04_27_200000_create_reservations_table.php`:

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
        Schema::create('reservations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('booking_ref', 20)->unique();         // CW-10001
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained('room_types');
            $table->foreignId('room_id')->nullable()->constrained('rooms');
            $table->foreignId('rate_plan_id')->constrained('rate_plans');
            $table->foreignId('guest_id')->constrained('guest_profiles');
            $table->string('status')->default('confirmed');       // ReservationStatus enum
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->unsignedTinyInteger('adults')->default(1);
            $table->unsignedTinyInteger('children')->default(0);
            $table->json('children_ages')->nullable();
            $table->decimal('base_rate', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->json('policy_snapshot');                      // Captured at booking time
            $table->string('source')->default('front_desk');     // front_desk, ota, web
            $table->string('market_segment')->nullable();
            $table->foreignId('company_id')->nullable();
            $table->foreignId('agent_id')->nullable();
            $table->text('special_requests')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['property_id', 'status']);
            $table->index(['check_in_date', 'check_out_date']);
            $table->index(['room_id', 'check_in_date', 'check_out_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
```

- [ ] **Step 1.4: Create reservation_guests migration**

Create `database/migrations/tenant/2026_04_27_200100_create_reservation_guests_table.php`:

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
        Schema::create('reservation_guests', function (Blueprint $table): void {
            $table->id();
            $table->uuid('reservation_id');
            $table->foreign('reservation_id')->references('id')->on('reservations')->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained('guest_profiles')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->index(['reservation_id', 'guest_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_guests');
    }
};
```

- [ ] **Step 1.5: Create reservation_audit_log migration**

Create `database/migrations/tenant/2026_04_27_200200_create_reservation_audit_log_table.php`:

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
        Schema::create('reservation_audit_log', function (Blueprint $table): void {
            $table->id();
            $table->uuid('reservation_id');
            $table->foreign('reservation_id')->references('id')->on('reservations')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');                            // created, checked_in, cancelled, etc.
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['reservation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_audit_log');
    }
};
```

- [ ] **Step 1.6: Create folios migration**

Create `database/migrations/tenant/2026_04_27_200300_create_folios_table.php`:

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
        Schema::create('folios', function (Blueprint $table): void {
            $table->id();
            $table->uuid('reservation_id')->unique();
            $table->foreign('reservation_id')->references('id')->on('reservations')->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained('guest_profiles');
            $table->string('status')->default('pending');        // FolioStatus enum
            $table->decimal('total_charges', 10, 2)->default(0);
            $table->decimal('total_payments', 10, 2)->default(0);
            $table->decimal('balance_due', 10, 2)->default(0);   // updated by triggers/service
            $table->char('currency', 3)->default('USD');
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'balance_due']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folios');
    }
};
```

- [ ] **Step 1.7: Create room_blocks migration**

Create `database/migrations/tenant/2026_04_27_200400_create_room_blocks_table.php`:

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
        Schema::create('room_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->enum('block_type', ['out_of_order', 'out_of_service', 'maintenance']);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['room_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_blocks');
    }
};
```

- [ ] **Step 1.8: Run migrations and verify**

```bash
php artisan migrate --path=database/migrations/tenant --database=mysql
```

Expected: 5 new tables — reservations, reservation_guests, reservation_audit_log, folios, room_blocks.

- [ ] **Step 1.9: Commit**

```bash
git add database/migrations/tenant/2026_04_27_200*.php app/Enums/ReservationStatus.php app/Enums/FolioStatus.php
git commit -m "feat: add reservation engine migrations and status enums"
```

---

## Task 2: Reservation Model & Relationships

**Files:**
- Create: `app/Modules/FrontDesk/Models/Reservation.php`
- Create: `app/Modules/FrontDesk/Models/ReservationGuest.php`
- Create: `app/Modules/FrontDesk/Models/ReservationAuditLog.php`
- Create: `app/Modules/FrontDesk/Models/Folio.php`
- Create: `app/Modules/FrontDesk/Models/RoomBlock.php`
- Test: `tests/Feature/FrontDesk/ReservationMigrationTest.php`

- [ ] **Step 2.1: Write failing test**

Create `tests/Feature/FrontDesk/ReservationMigrationTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\ReservationStatus;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Models\Folio;
use Illuminate\Support\Str;

it('creates a reservation with required fields', function (): void {
    $reservation = Reservation::create([
        'id'              => Str::uuid(),
        'booking_ref'     => 'CW-10001',
        'property_id'     => 1,
        'room_type_id'    => 1,
        'rate_plan_id'    => 1,
        'guest_id'        => 1,
        'status'          => ReservationStatus::Confirmed->value,
        'check_in_date'   => '2026-05-01',
        'check_out_date'  => '2026-05-03',
        'adults'          => 2,
        'base_rate'       => 150.00,
        'total_amount'    => 300.00,
        'policy_snapshot' => ['cancellation_deadline' => 24, 'penalty_type' => 'first_night'],
    ]);

    expect($reservation->booking_ref)->toBe('CW-10001')
        ->and($reservation->status)->toBe(ReservationStatus::Confirmed->value)
        ->and($reservation->folio)->toBeNull(); // no folio yet
});

it('has correct status enum helpers', function (): void {
    expect(ReservationStatus::Confirmed->canCheckIn())->toBeTrue()
        ->and(ReservationStatus::CheckedIn->canCheckOut())->toBeTrue()
        ->and(ReservationStatus::CheckedOut->canCancel())->toBeFalse();
});
```

- [ ] **Step 2.2: Run test to verify it fails**

```bash
php artisan test tests/Feature/FrontDesk/ReservationMigrationTest.php
```

Expected: FAIL — `App\Modules\FrontDesk\Models\Reservation` not found.

- [ ] **Step 2.3: Create Reservation model**

Create `app/Modules/FrontDesk/Models/Reservation.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'booking_ref', 'property_id', 'room_type_id', 'room_id',
        'rate_plan_id', 'guest_id', 'status', 'check_in_date', 'check_out_date',
        'adults', 'children', 'children_ages', 'base_rate', 'total_amount',
        'tax_amount', 'discount_amount', 'policy_snapshot', 'source',
        'market_segment', 'company_id', 'agent_id', 'special_requests',
        'internal_notes', 'checked_in_at', 'checked_out_at', 'created_by',
    ];

    protected $casts = [
        'check_in_date'   => 'date',
        'check_out_date'  => 'date',
        'children_ages'   => 'array',
        'policy_snapshot' => 'array',
        'checked_in_at'   => 'datetime',
        'checked_out_at'  => 'datetime',
        'base_rate'       => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'status'          => ReservationStatus::class,
    ];

    public function folio(): HasOne
    {
        return $this->hasOne(Folio::class, 'reservation_id');
    }

    public function guests(): HasMany
    {
        return $this->hasMany(ReservationGuest::class, 'reservation_id');
    }

    public function auditLog(): HasMany
    {
        return $this->hasMany(ReservationAuditLog::class, 'reservation_id');
    }

    public function nights(): int
    {
        return $this->check_in_date->diffInDays($this->check_out_date);
    }
}
```

- [ ] **Step 2.4: Create supporting models**

Create `app/Modules/FrontDesk/Models/ReservationGuest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationGuest extends Model
{
    protected $fillable = ['reservation_id', 'guest_id', 'is_primary'];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }
}
```

Create `app/Modules/FrontDesk/Models/ReservationAuditLog.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationAuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['reservation_id', 'user_id', 'action', 'before', 'after', 'ip_address'];

    protected $casts = ['before' => 'array', 'after' => 'array'];
}
```

Create `app/Modules/FrontDesk/Models/Folio.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use App\Enums\FolioStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Folio extends Model
{
    protected $fillable = [
        'reservation_id', 'guest_id', 'status',
        'total_charges', 'total_payments', 'balance_due', 'currency', 'settled_at',
    ];

    protected $casts = [
        'status'         => FolioStatus::class,
        'total_charges'  => 'decimal:2',
        'total_payments' => 'decimal:2',
        'balance_due'    => 'decimal:2',
        'settled_at'     => 'datetime',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }
}
```

Create `app/Modules/FrontDesk/Models/RoomBlock.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomBlock extends Model
{
    protected $fillable = ['room_id', 'property_id', 'block_type', 'start_date', 'end_date', 'reason', 'created_by'];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
```

- [ ] **Step 2.5: Run test to verify it passes**

```bash
php artisan test tests/Feature/FrontDesk/ReservationMigrationTest.php
```

Expected: 2 tests PASS.

- [ ] **Step 2.6: Commit**

```bash
git add app/Modules/FrontDesk/Models/ app/Enums/ tests/Feature/FrontDesk/ReservationMigrationTest.php
git commit -m "feat: add Reservation, Folio, RoomBlock models with status enums"
```

---

## Task 3: Availability Check Engine

**Files:**
- Create: `app/Modules/FrontDesk/Actions/CheckAvailabilityAction.php`
- Test: `tests/Feature/FrontDesk/AvailabilityTest.php`

- [ ] **Step 3.1: Write failing test**

Create `tests/Feature/FrontDesk/AvailabilityTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\ReservationStatus;
use App\Modules\FrontDesk\Actions\CheckAvailabilityAction;
use App\Modules\FrontDesk\Models\Reservation;
use Illuminate\Support\Str;

it('returns available when no conflicting reservation', function (): void {
    $action = new CheckAvailabilityAction();

    $available = $action->execute(
        roomId: 1,
        checkIn: '2026-06-01',
        checkOut: '2026-06-03',
    );

    expect($available)->toBeTrue();
});

it('returns unavailable when room is already booked for overlap', function (): void {
    Reservation::create([
        'id'             => Str::uuid(),
        'booking_ref'    => 'CW-00001',
        'property_id'    => 1,
        'room_type_id'   => 1,
        'room_id'        => 1,
        'rate_plan_id'   => 1,
        'guest_id'       => 1,
        'status'         => ReservationStatus::CheckedIn->value,
        'check_in_date'  => '2026-06-01',
        'check_out_date' => '2026-06-05',
        'adults'         => 2,
        'base_rate'      => 100,
        'total_amount'   => 400,
        'policy_snapshot'=> [],
    ]);

    $action = new CheckAvailabilityAction();

    expect($action->execute(roomId: 1, checkIn: '2026-06-03', checkOut: '2026-06-07'))->toBeFalse()
        ->and($action->execute(roomId: 1, checkIn: '2026-05-30', checkOut: '2026-06-02'))->toBeFalse()
        ->and($action->execute(roomId: 2, checkIn: '2026-06-03', checkOut: '2026-06-07'))->toBeTrue();
});

it('excludes cancelled and checked_out reservations from availability', function (): void {
    Reservation::create([
        'id'             => Str::uuid(),
        'booking_ref'    => 'CW-00002',
        'property_id'    => 1,
        'room_type_id'   => 1,
        'room_id'        => 1,
        'rate_plan_id'   => 1,
        'guest_id'       => 1,
        'status'         => ReservationStatus::Cancelled->value,
        'check_in_date'  => '2026-07-01',
        'check_out_date' => '2026-07-05',
        'adults'         => 1,
        'base_rate'      => 100,
        'total_amount'   => 400,
        'policy_snapshot'=> [],
    ]);

    $action = new CheckAvailabilityAction();
    expect($action->execute(roomId: 1, checkIn: '2026-07-01', checkOut: '2026-07-03'))->toBeTrue();
});
```

- [ ] **Step 3.2: Run test to verify it fails**

```bash
php artisan test tests/Feature/FrontDesk/AvailabilityTest.php
```

Expected: FAIL — `App\Modules\FrontDesk\Actions\CheckAvailabilityAction` not found.

- [ ] **Step 3.3: Create CheckAvailabilityAction**

Create `app/Modules/FrontDesk/Actions/CheckAvailabilityAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

use App\Enums\ReservationStatus;
use App\Modules\FrontDesk\Models\RoomBlock;
use Illuminate\Support\Facades\DB;

class CheckAvailabilityAction
{
    public function execute(
        int    $roomId,
        string $checkIn,
        string $checkOut,
        ?string $excludeReservationId = null
    ): bool {
        // Check active reservations — uses row-level lock in transactional context
        $reservationConflict = DB::table('reservations')
            ->where('room_id', $roomId)
            ->whereNotIn('status', [
                ReservationStatus::Cancelled->value,
                ReservationStatus::NoShow->value,
                ReservationStatus::CheckedOut->value,
            ])
            ->when($excludeReservationId, fn ($q) => $q->where('id', '!=', $excludeReservationId))
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn)
            ->whereNull('deleted_at')
            ->lockForUpdate()
            ->exists();

        if ($reservationConflict) {
            return false;
        }

        // Check room blocks
        $blockConflict = RoomBlock::where('room_id', $roomId)
            ->where('start_date', '<', $checkOut)
            ->where('end_date', '>', $checkIn)
            ->exists();

        return ! $blockConflict;
    }
}
```

- [ ] **Step 3.4: Run test to verify it passes**

```bash
php artisan test tests/Feature/FrontDesk/AvailabilityTest.php
```

Expected: 3 tests PASS.

- [ ] **Step 3.5: Commit**

```bash
git add app/Modules/FrontDesk/Actions/CheckAvailabilityAction.php tests/Feature/FrontDesk/AvailabilityTest.php
git commit -m "feat: add CheckAvailabilityAction with row-level locking and block detection"
```

---

## Task 4: OBP Pricing Calculation Engine

**Files:**
- Create: `app/Modules/FrontDesk/Actions/CalculatePricingAction.php`
- Test: `tests/Feature/FrontDesk/PricingEngineTest.php`

- [ ] **Step 4.1: Write failing test**

Create `tests/Feature/FrontDesk/PricingEngineTest.php`:

```php
<?php

declare(strict_types=1);

use App\Modules\FrontDesk\Actions\CalculatePricingAction;

it('calculates standard 2-adult rate correctly', function (): void {
    // PricingProfile: standard_occupancy=2, extra_adult_fee=20, single_discount=10 (fixed)
    $profile = [
        'standard_occupancy'  => 2,
        'single_discount_type' => 'fixed',
        'single_discount_value' => 10.00,
        'extra_adult_fee'     => 20.00,
        'child_age_buckets'   => [
            ['min_age' => 0,  'max_age' => 5,  'fee' => 0],
            ['min_age' => 6,  'max_age' => 12, 'fee' => 10],
            ['min_age' => 13, 'max_age' => 17, 'fee' => 15],
        ],
    ];

    $action = new CalculatePricingAction();

    // 2 adults, base 150/night → 150 × 2 nights = 300
    expect($action->calculate(150.00, 2, [], $profile, 2))->toBe(300.00);

    // 1 adult, single discount -10 → (150-10) × 2 nights = 280
    expect($action->calculate(150.00, 1, [], $profile, 2))->toBe(280.00);

    // 3 adults → (150 + 20 extra) × 2 nights = 340
    expect($action->calculate(150.00, 3, [], $profile, 2))->toBe(340.00);
});

it('applies child fees by age bucket', function (): void {
    $profile = [
        'standard_occupancy'   => 2,
        'single_discount_type' => 'fixed',
        'single_discount_value'=> 0,
        'extra_adult_fee'      => 0,
        'child_age_buckets'    => [
            ['min_age' => 0,  'max_age' => 5,  'fee' => 0],
            ['min_age' => 6,  'max_age' => 12, 'fee' => 10],
        ],
    ];

    $action = new CalculatePricingAction();

    // 2 adults + child aged 8 (fee=10) × 2 nights = 300 + 20 = 320
    expect($action->calculate(150.00, 2, [8], $profile, 2))->toBe(320.00);

    // 2 adults + child aged 3 (free) × 2 nights = 300
    expect($action->calculate(150.00, 2, [3], $profile, 2))->toBe(300.00);
});
```

- [ ] **Step 4.2: Run test to verify it fails**

```bash
php artisan test tests/Feature/FrontDesk/PricingEngineTest.php
```

Expected: FAIL.

- [ ] **Step 4.3: Create CalculatePricingAction**

Create `app/Modules/FrontDesk/Actions/CalculatePricingAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

class CalculatePricingAction
{
    public function calculate(
        float $baseRatePerNight,
        int   $adults,
        array $childrenAges,
        array $pricingProfile,
        int   $nights
    ): float {
        $nightly = $baseRatePerNight;

        // Single occupancy discount
        if ($adults === 1 && $pricingProfile['standard_occupancy'] >= 2) {
            $discount = $pricingProfile['single_discount_value'] ?? 0;
            $nightly  = $pricingProfile['single_discount_type'] === 'percentage'
                ? $nightly * (1 - $discount / 100)
                : $nightly - $discount;
        }

        // Extra adults above standard occupancy
        $extraAdults = max(0, $adults - ($pricingProfile['standard_occupancy'] ?? 2));
        $nightly    += $extraAdults * ($pricingProfile['extra_adult_fee'] ?? 0);

        // Child fees per age bucket
        foreach ($childrenAges as $age) {
            foreach ($pricingProfile['child_age_buckets'] ?? [] as $bucket) {
                if ($age >= $bucket['min_age'] && $age <= $bucket['max_age']) {
                    $nightly += (float) $bucket['fee'];
                    break;
                }
            }
        }

        return round($nightly * $nights, 2);
    }
}
```

- [ ] **Step 4.4: Run test to verify it passes**

```bash
php artisan test tests/Feature/FrontDesk/PricingEngineTest.php
```

Expected: 2 tests PASS.

- [ ] **Step 4.5: Commit**

```bash
git add app/Modules/FrontDesk/Actions/CalculatePricingAction.php tests/Feature/FrontDesk/PricingEngineTest.php
git commit -m "feat: add OBP pricing calculation engine with occupancy modifiers"
```

---

## Task 5: Policy Snapshot & Folio Initialization

**Files:**
- Create: `app/Modules/FrontDesk/Actions/CaptureBookingReferenceAction.php`
- Create: `app/Modules/FrontDesk/Actions/CapturePoilcySnapshotAction.php`
- Create: `app/Modules/FrontDesk/Actions/InitializeFolioAction.php`

- [ ] **Step 5.1: Create CaptureBookingReferenceAction**

Create `app/Modules/FrontDesk/Actions/CaptureBookingReferenceAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

use Illuminate\Support\Facades\DB;

class CaptureBookingReferenceAction
{
    public function execute(): string
    {
        $last = DB::table('reservations')
            ->selectRaw('MAX(CAST(SUBSTRING(booking_ref, 4) AS UNSIGNED)) as last_num')
            ->value('last_num');

        $next = ($last ?? 10000) + 1;

        return 'CW-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
```

- [ ] **Step 5.2: Create CapturePoilcySnapshotAction**

Create `app/Modules/FrontDesk/Actions/CapturePoilcySnapshotAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

use App\Modules\RateAvailability\Models\CancellationPolicy;

class CapturePoilcySnapshotAction
{
    public function execute(int $policyId): array
    {
        $policy = CancellationPolicy::findOrFail($policyId);

        return [
            'policy_id'             => $policy->id,
            'policy_name'           => $policy->name,
            'cancellation_deadline' => $policy->cancellation_deadline_hours,
            'penalty_type'          => $policy->penalty_type,
            'penalty_value'         => $policy->penalty_value,
            'no_show_penalty_type'  => $policy->no_show_penalty_type,
            'no_show_penalty_value' => $policy->no_show_penalty_value,
            'captured_at'           => now()->toIso8601String(),
        ];
    }
}
```

- [ ] **Step 5.3: Create InitializeFolioAction**

Create `app/Modules/FrontDesk/Actions/InitializeFolioAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

use App\Enums\FolioStatus;
use App\Modules\FrontDesk\Models\Folio;

class InitializeFolioAction
{
    public function execute(string $reservationId, int $guestId, string $currency = 'USD'): Folio
    {
        return Folio::create([
            'reservation_id' => $reservationId,
            'guest_id'       => $guestId,
            'status'         => FolioStatus::Pending,
            'currency'       => $currency,
            'total_charges'  => 0,
            'total_payments' => 0,
            'balance_due'    => 0,
        ]);
    }
}
```

- [ ] **Step 5.4: Commit**

```bash
git add app/Modules/FrontDesk/Actions/CaptureBookingReferenceAction.php \
        app/Modules/FrontDesk/Actions/CapturePoilcySnapshotAction.php \
        app/Modules/FrontDesk/Actions/InitializeFolioAction.php
git commit -m "feat: add booking reference generator, policy snapshot, and folio initializer"
```

---

## Task 6: CreateReservationAction — Full Transactional Booking

**Files:**
- Create: `app/Modules/FrontDesk/Actions/CreateReservationAction.php`
- Test: `tests/Feature/FrontDesk/CreateReservationTest.php`

- [ ] **Step 6.1: Write failing test**

Create `tests/Feature/FrontDesk/CreateReservationTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\ReservationStatus;
use App\Modules\FrontDesk\Actions\CreateReservationAction;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Models\Folio;

it('creates reservation and folio atomically', function (): void {
    $action = app(CreateReservationAction::class);

    $reservation = $action->execute([
        'property_id'    => 1,
        'room_type_id'   => 1,
        'room_id'        => 1,
        'rate_plan_id'   => 1,
        'guest_id'       => 1,
        'check_in_date'  => '2026-08-01',
        'check_out_date' => '2026-08-03',
        'adults'         => 2,
        'children'       => 0,
        'children_ages'  => [],
        'base_rate'      => 150.00,
        'source'         => 'front_desk',
    ]);

    expect($reservation)->toBeInstanceOf(Reservation::class)
        ->and($reservation->status->value)->toBe(ReservationStatus::Confirmed->value)
        ->and($reservation->booking_ref)->toStartWith('CW-')
        ->and(Folio::where('reservation_id', $reservation->id)->exists())->toBeTrue();
});

it('rolls back if folio creation fails', function (): void {
    // Force folio creation to fail by providing invalid guest_id
    $action = app(CreateReservationAction::class);

    expect(fn () => $action->execute([
        'property_id'    => 1,
        'room_type_id'   => 1,
        'room_id'        => 1,
        'rate_plan_id'   => 1,
        'guest_id'       => 99999, // non-existent
        'check_in_date'  => '2026-09-01',
        'check_out_date' => '2026-09-03',
        'adults'         => 1,
        'base_rate'      => 100.00,
        'source'         => 'front_desk',
    ]))->toThrow(\Exception::class);

    expect(Reservation::where('check_in_date', '2026-09-01')->exists())->toBeFalse();
});
```

- [ ] **Step 6.2: Run test to verify it fails**

```bash
php artisan test tests/Feature/FrontDesk/CreateReservationTest.php
```

Expected: FAIL.

- [ ] **Step 6.3: Create CreateReservationAction**

Create `app/Modules/FrontDesk/Actions/CreateReservationAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

use App\Enums\FolioStatus;
use App\Enums\ReservationStatus;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Models\ReservationAuditLog;
use App\Modules\RateAvailability\Models\RatePlan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateReservationAction
{
    public function __construct(
        private readonly CheckAvailabilityAction       $availability,
        private readonly CalculatePricingAction        $pricing,
        private readonly CapturePoilcySnapshotAction   $policySnapshot,
        private readonly CaptureBookingReferenceAction $bookingRef,
        private readonly InitializeFolioAction         $initFolio,
    ) {}

    public function execute(array $data): Reservation
    {
        return DB::transaction(function () use ($data): Reservation {
            // 1. Availability check with row-level lock
            if (isset($data['room_id']) && ! $this->availability->execute(
                roomId:   $data['room_id'],
                checkIn:  $data['check_in_date'],
                checkOut: $data['check_out_date'],
            )) {
                throw new \RuntimeException("Room {$data['room_id']} is not available for the selected dates.");
            }

            // 2. Capture policy snapshot
            $ratePlan       = RatePlan::findOrFail($data['rate_plan_id']);
            $policySnapshot = $this->policySnapshot->execute($ratePlan->cancellation_policy_id);

            // 3. Calculate total
            $nights       = (int) (new \DateTime($data['check_out_date']))->diff(new \DateTime($data['check_in_date']))->days;
            $pricingProfile = $ratePlan->pricingProfile?->toArray() ?? [];
            $totalAmount  = $this->pricing->calculate(
                $data['base_rate'],
                $data['adults'] ?? 1,
                $data['children_ages'] ?? [],
                $pricingProfile,
                $nights,
            );

            // 4. Create reservation
            $reservation = Reservation::create([
                'id'              => Str::uuid(),
                'booking_ref'     => $this->bookingRef->execute(),
                'property_id'     => $data['property_id'],
                'room_type_id'    => $data['room_type_id'],
                'room_id'         => $data['room_id'] ?? null,
                'rate_plan_id'    => $data['rate_plan_id'],
                'guest_id'        => $data['guest_id'],
                'status'          => ReservationStatus::Confirmed,
                'check_in_date'   => $data['check_in_date'],
                'check_out_date'  => $data['check_out_date'],
                'adults'          => $data['adults'] ?? 1,
                'children'        => $data['children'] ?? 0,
                'children_ages'   => $data['children_ages'] ?? [],
                'base_rate'       => $data['base_rate'],
                'total_amount'    => $totalAmount,
                'policy_snapshot' => $policySnapshot,
                'source'          => $data['source'] ?? 'front_desk',
                'market_segment'  => $data['market_segment'] ?? null,
                'company_id'      => $data['company_id'] ?? null,
                'agent_id'        => $data['agent_id'] ?? null,
                'special_requests'=> $data['special_requests'] ?? null,
                'created_by'      => Auth::id(),
            ]);

            // 5. Initialize folio atomically
            $this->initFolio->execute($reservation->id, $data['guest_id']);

            // 6. Audit log
            ReservationAuditLog::create([
                'reservation_id' => $reservation->id,
                'user_id'        => Auth::id(),
                'action'         => 'created',
                'after'          => $reservation->toArray(),
                'ip_address'     => request()->ip(),
            ]);

            return $reservation;
        });
    }
}
```

- [ ] **Step 6.4: Run test to verify it passes**

```bash
php artisan test tests/Feature/FrontDesk/CreateReservationTest.php
```

Expected: 2 tests PASS.

- [ ] **Step 6.5: Commit**

```bash
git add app/Modules/FrontDesk/Actions/CreateReservationAction.php tests/Feature/FrontDesk/CreateReservationTest.php
git commit -m "feat: add CreateReservationAction with transactional booking, availability lock, OBP pricing"
```

---

## Task 7: Status Machine Actions

**Files:**
- Create: `app/Modules/FrontDesk/Actions/CheckInAction.php`
- Create: `app/Modules/FrontDesk/Actions/CheckOutAction.php`
- Create: `app/Modules/FrontDesk/Actions/CancelReservationAction.php`
- Create: `app/Modules/FrontDesk/Actions/MarkNoShowAction.php`
- Test: `tests/Feature/FrontDesk/ReservationStatusTest.php`

- [ ] **Step 7.1: Write failing tests**

Create `tests/Feature/FrontDesk/ReservationStatusTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\FolioStatus;
use App\Enums\ReservationStatus;
use App\Modules\FrontDesk\Actions\CheckInAction;
use App\Modules\FrontDesk\Actions\CheckOutAction;
use App\Modules\FrontDesk\Actions\CancelReservationAction;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Models\Folio;
use Illuminate\Support\Str;

function makeReservation(string $status = 'confirmed', array $overrides = []): Reservation
{
    $res = Reservation::create(array_merge([
        'id'             => Str::uuid(),
        'booking_ref'    => 'CW-' . rand(10000, 99999),
        'property_id'    => 1,
        'room_type_id'   => 1,
        'room_id'        => 1,
        'rate_plan_id'   => 1,
        'guest_id'       => 1,
        'status'         => $status,
        'check_in_date'  => now()->toDateString(),
        'check_out_date' => now()->addDays(2)->toDateString(),
        'adults'         => 2,
        'base_rate'      => 100,
        'total_amount'   => 200,
        'policy_snapshot'=> ['cancellation_deadline' => 24, 'penalty_type' => 'none'],
    ], $overrides));

    Folio::create(['reservation_id' => $res->id, 'guest_id' => 1, 'status' => FolioStatus::Pending, 'currency' => 'USD']);

    return $res;
}

it('checks in a confirmed reservation', function (): void {
    $res = makeReservation('confirmed');
    app(CheckInAction::class)->execute($res);

    $res->refresh();
    expect($res->status->value)->toBe(ReservationStatus::CheckedIn->value)
        ->and($res->checked_in_at)->not->toBeNull()
        ->and($res->folio->status->value)->toBe(FolioStatus::Active->value);
});

it('throws when checking in a non-confirmed reservation', function (): void {
    $res = makeReservation('checked_in');
    expect(fn () => app(CheckInAction::class)->execute($res))->toThrow(\RuntimeException::class);
});

it('checks out a checked-in reservation', function (): void {
    $res = makeReservation('checked_in');
    app(CheckOutAction::class)->execute($res);

    $res->refresh();
    expect($res->status->value)->toBe(ReservationStatus::CheckedOut->value)
        ->and($res->checked_out_at)->not->toBeNull();
});

it('cancels a confirmed reservation', function (): void {
    $res = makeReservation('confirmed');
    app(CancelReservationAction::class)->execute($res, reason: 'Guest request');

    $res->refresh();
    expect($res->status->value)->toBe(ReservationStatus::Cancelled->value);
});
```

- [ ] **Step 7.2: Run test to verify it fails**

```bash
php artisan test tests/Feature/FrontDesk/ReservationStatusTest.php
```

Expected: FAIL.

- [ ] **Step 7.3: Create CheckInAction**

Create `app/Modules/FrontDesk/Actions/CheckInAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

use App\Enums\FolioStatus;
use App\Enums\ReservationStatus;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Models\ReservationAuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckInAction
{
    public function execute(Reservation $reservation): void
    {
        if (! $reservation->status->canCheckIn()) {
            throw new \RuntimeException("Cannot check in a reservation with status: {$reservation->status->value}");
        }

        DB::transaction(function () use ($reservation): void {
            $before = $reservation->status->value;

            $reservation->update([
                'status'        => ReservationStatus::CheckedIn,
                'checked_in_at' => now(),
            ]);

            $reservation->folio?->update(['status' => FolioStatus::Active]);

            // Update room status
            $reservation->room?->update(['status' => 'occupied']);

            ReservationAuditLog::create([
                'reservation_id' => $reservation->id,
                'user_id'        => Auth::id(),
                'action'         => 'checked_in',
                'before'         => ['status' => $before],
                'after'          => ['status' => ReservationStatus::CheckedIn->value],
                'ip_address'     => request()->ip(),
            ]);
        });
    }
}
```

- [ ] **Step 7.4: Create CheckOutAction**

Create `app/Modules/FrontDesk/Actions/CheckOutAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

use App\Enums\ReservationStatus;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Models\ReservationAuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckOutAction
{
    public function execute(Reservation $reservation): void
    {
        if (! $reservation->status->canCheckOut()) {
            throw new \RuntimeException("Cannot check out reservation with status: {$reservation->status->value}");
        }

        DB::transaction(function () use ($reservation): void {
            $reservation->update([
                'status'          => ReservationStatus::CheckedOut,
                'checked_out_at'  => now(),
            ]);

            // Room → dirty (HK picks this up via ReservationObserver)
            $reservation->room?->update(['hk_status' => 'dirty', 'status' => 'vacant']);

            ReservationAuditLog::create([
                'reservation_id' => $reservation->id,
                'user_id'        => Auth::id(),
                'action'         => 'checked_out',
                'before'         => ['status' => ReservationStatus::CheckedIn->value],
                'after'          => ['status' => ReservationStatus::CheckedOut->value],
                'ip_address'     => request()->ip(),
            ]);
        });
    }
}
```

- [ ] **Step 7.5: Create CancelReservationAction**

Create `app/Modules/FrontDesk/Actions/CancelReservationAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

use App\Enums\ReservationStatus;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Models\ReservationAuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CancelReservationAction
{
    public function execute(Reservation $reservation, string $reason = ''): void
    {
        if (! $reservation->status->canCancel()) {
            throw new \RuntimeException("Cannot cancel reservation with status: {$reservation->status->value}");
        }

        DB::transaction(function () use ($reservation, $reason): void {
            $before = $reservation->status->value;

            $reservation->update(['status' => ReservationStatus::Cancelled]);

            if ($reservation->room_id) {
                $reservation->room?->update(['status' => 'vacant']);
            }

            ReservationAuditLog::create([
                'reservation_id' => $reservation->id,
                'user_id'        => Auth::id(),
                'action'         => 'cancelled',
                'before'         => ['status' => $before],
                'after'          => ['status' => ReservationStatus::Cancelled->value, 'reason' => $reason],
                'ip_address'     => request()->ip(),
            ]);
        });
    }
}
```

- [ ] **Step 7.6: Create MarkNoShowAction**

Create `app/Modules/FrontDesk/Actions/MarkNoShowAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

use App\Enums\ReservationStatus;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Models\ReservationAuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarkNoShowAction
{
    public function execute(Reservation $reservation): void
    {
        if ($reservation->status !== ReservationStatus::Confirmed
            && $reservation->status !== ReservationStatus::WaitingConfirmation) {
            throw new \RuntimeException("Cannot mark no-show for status: {$reservation->status->value}");
        }

        DB::transaction(function () use ($reservation): void {
            $reservation->update(['status' => ReservationStatus::NoShow]);

            // Post no-show fee from policy snapshot if applicable
            $snapshot = $reservation->policy_snapshot;
            // Fee posting is handled by Phase 5 PostChargeAction — placeholder for now.

            ReservationAuditLog::create([
                'reservation_id' => $reservation->id,
                'user_id'        => Auth::id() ?? 0,
                'action'         => 'no_show',
                'before'         => ['status' => $reservation->getOriginal('status')],
                'after'          => ['status' => ReservationStatus::NoShow->value],
                'ip_address'     => request()->ip(),
            ]);
        });
    }
}
```

- [ ] **Step 7.7: Run tests to verify they pass**

```bash
php artisan test tests/Feature/FrontDesk/ReservationStatusTest.php
```

Expected: 4 tests PASS.

- [ ] **Step 7.8: Commit**

```bash
git add app/Modules/FrontDesk/Actions/CheckInAction.php \
        app/Modules/FrontDesk/Actions/CheckOutAction.php \
        app/Modules/FrontDesk/Actions/CancelReservationAction.php \
        app/Modules/FrontDesk/Actions/MarkNoShowAction.php \
        tests/Feature/FrontDesk/ReservationStatusTest.php
git commit -m "feat: add CheckIn, CheckOut, Cancel, NoShow status machine actions"
```

---

## Task 8: ReservationService + API Controller + Routes

**Files:**
- Create: `app/Modules/FrontDesk/Services/ReservationService.php`
- Create: `app/Modules/FrontDesk/Controllers/Api/V1/ReservationController.php`
- Create: `app/Modules/FrontDesk/Requests/StoreReservationRequest.php`
- Create: `app/Modules/FrontDesk/Requests/UpdateReservationRequest.php`
- Create: `app/Modules/FrontDesk/Resources/ReservationResource.php`
- Modify: `routes/tenant-api.php`

- [ ] **Step 8.1: Create ReservationService**

Create `app/Modules/FrontDesk/Services/ReservationService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Services;

use App\Modules\FrontDesk\Actions\CancelReservationAction;
use App\Modules\FrontDesk\Actions\CheckInAction;
use App\Modules\FrontDesk\Actions\CheckOutAction;
use App\Modules\FrontDesk\Actions\CreateReservationAction;
use App\Modules\FrontDesk\Actions\MarkNoShowAction;
use App\Modules\FrontDesk\Models\Reservation;
use Illuminate\Pagination\LengthAwarePaginator;

readonly class ReservationService
{
    public function __construct(
        private CreateReservationAction $create,
        private CheckInAction           $checkIn,
        private CheckOutAction          $checkOut,
        private CancelReservationAction $cancel,
        private MarkNoShowAction        $noShow,
    ) {}

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return Reservation::query()
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['date'] ?? null, fn ($q, $d) => $q->whereDate('check_in_date', $d))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where('booking_ref', 'like', "%$s%"))
            ->with(['folio'])
            ->latest()
            ->paginate(20);
    }

    public function create(array $data): Reservation
    {
        return $this->create->execute($data);
    }

    public function checkIn(Reservation $reservation): void
    {
        $this->checkIn->execute($reservation);
    }

    public function checkOut(Reservation $reservation): void
    {
        $this->checkOut->execute($reservation);
    }

    public function cancel(Reservation $reservation, string $reason = ''): void
    {
        $this->cancel->execute($reservation, $reason);
    }

    public function markNoShow(Reservation $reservation): void
    {
        $this->noShow->execute($reservation);
    }

    public function arrivalsToday(int $propertyId): \Illuminate\Database\Eloquent\Collection
    {
        return Reservation::where('property_id', $propertyId)
            ->whereDate('check_in_date', today())
            ->whereIn('status', ['confirmed', 'waiting_confirmation'])
            ->with(['folio'])
            ->get();
    }

    public function inHouse(int $propertyId): \Illuminate\Database\Eloquent\Collection
    {
        return Reservation::where('property_id', $propertyId)
            ->where('status', 'checked_in')
            ->with(['folio'])
            ->get();
    }

    public function departuresToday(int $propertyId): \Illuminate\Database\Eloquent\Collection
    {
        return Reservation::where('property_id', $propertyId)
            ->whereDate('check_out_date', today())
            ->whereIn('status', ['checked_in', 'checked_out'])
            ->with(['folio'])
            ->get();
    }
}
```

- [ ] **Step 8.2: Create ReservationResource**

Create `app/Modules/FrontDesk/Resources/ReservationResource.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'booking_ref'      => $this->booking_ref,
            'status'           => $this->status->value,
            'status_label'     => $this->status->label(),
            'check_in_date'    => $this->check_in_date->toDateString(),
            'check_out_date'   => $this->check_out_date->toDateString(),
            'nights'           => $this->nights(),
            'adults'           => $this->adults,
            'children'         => $this->children,
            'base_rate'        => $this->base_rate,
            'total_amount'     => $this->total_amount,
            'source'           => $this->source,
            'market_segment'   => $this->market_segment,
            'special_requests' => $this->special_requests,
            'checked_in_at'    => $this->checked_in_at?->toIso8601String(),
            'checked_out_at'   => $this->checked_out_at?->toIso8601String(),
            'folio'            => $this->whenLoaded('folio', fn () => [
                'id'          => $this->folio->id,
                'status'      => $this->folio->status->value,
                'balance_due' => $this->folio->balance_due,
            ]),
            'created_at'       => $this->created_at->toIso8601String(),
        ];
    }
}
```

- [ ] **Step 8.3: Create StoreReservationRequest**

Create `app/Modules/FrontDesk/Requests/StoreReservationRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'property_id'     => ['required', 'integer', 'exists:properties,id'],
            'room_type_id'    => ['required', 'integer', 'exists:room_types,id'],
            'room_id'         => ['nullable', 'integer', 'exists:rooms,id'],
            'rate_plan_id'    => ['required', 'integer', 'exists:rate_plans,id'],
            'guest_id'        => ['required', 'integer', 'exists:guest_profiles,id'],
            'check_in_date'   => ['required', 'date', 'after_or_equal:today'],
            'check_out_date'  => ['required', 'date', 'after:check_in_date'],
            'adults'          => ['required', 'integer', 'min:1', 'max:10'],
            'children'        => ['nullable', 'integer', 'min:0'],
            'children_ages'   => ['nullable', 'array'],
            'children_ages.*' => ['integer', 'min:0', 'max:17'],
            'base_rate'       => ['required', 'numeric', 'min:0'],
            'source'          => ['nullable', 'string', 'in:front_desk,ota,web,phone'],
            'market_segment'  => ['nullable', 'string'],
            'special_requests'=> ['nullable', 'string', 'max:1000'],
        ];
    }
}
```

- [ ] **Step 8.4: Create API ReservationController**

Create `app/Modules/FrontDesk/Controllers/Api/V1/ReservationController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Requests\StoreReservationRequest;
use App\Modules\FrontDesk\Resources\ReservationResource;
use App\Modules\FrontDesk\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(private readonly ReservationService $service) {}

    public function index(Request $request): JsonResponse
    {
        $reservations = $this->service->paginate($request->only('status', 'date', 'search'));
        return response()->json(['status' => 1, 'data' => ReservationResource::collection($reservations), 'message' => '']);
    }

    public function store(StoreReservationRequest $request): JsonResponse
    {
        $reservation = $this->service->create($request->validated());
        return response()->json(['status' => 1, 'data' => new ReservationResource($reservation), 'message' => 'Reservation created.'], 201);
    }

    public function show(Reservation $reservation): JsonResponse
    {
        $reservation->load('folio', 'guests');
        return response()->json(['status' => 1, 'data' => new ReservationResource($reservation), 'message' => '']);
    }

    public function checkIn(Reservation $reservation): JsonResponse
    {
        $this->service->checkIn($reservation);
        return response()->json(['status' => 1, 'data' => new ReservationResource($reservation->refresh()), 'message' => 'Checked in.']);
    }

    public function checkOut(Reservation $reservation): JsonResponse
    {
        $this->service->checkOut($reservation);
        return response()->json(['status' => 1, 'data' => new ReservationResource($reservation->refresh()), 'message' => 'Checked out.']);
    }

    public function cancel(Request $request, Reservation $reservation): JsonResponse
    {
        $this->service->cancel($reservation, $request->input('reason', ''));
        return response()->json(['status' => 1, 'data' => new ReservationResource($reservation->refresh()), 'message' => 'Cancelled.']);
    }

    public function arrivals(Request $request): JsonResponse
    {
        $data = $this->service->arrivalsToday($request->input('property_id'));
        return response()->json(['status' => 1, 'data' => ReservationResource::collection($data), 'message' => '']);
    }

    public function inHouse(Request $request): JsonResponse
    {
        $data = $this->service->inHouse($request->input('property_id'));
        return response()->json(['status' => 1, 'data' => ReservationResource::collection($data), 'message' => '']);
    }

    public function departures(Request $request): JsonResponse
    {
        $data = $this->service->departuresToday($request->input('property_id'));
        return response()->json(['status' => 1, 'data' => ReservationResource::collection($data), 'message' => '']);
    }
}
```

- [ ] **Step 8.5: Register routes in tenant-api.php**

In `routes/tenant-api.php`, add inside the `auth:sanctum` group:

```php
Route::prefix('front-desk')->name('front-desk.')->group(function (): void {
    Route::apiResource('reservations', ReservationController::class)->except(['update', 'destroy']);
    Route::post('reservations/{reservation}/check-in',  [ReservationController::class, 'checkIn'])->name('reservations.check-in');
    Route::post('reservations/{reservation}/check-out', [ReservationController::class, 'checkOut'])->name('reservations.check-out');
    Route::post('reservations/{reservation}/cancel',    [ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::get('arrivals',   [ReservationController::class, 'arrivals'])->name('arrivals');
    Route::get('in-house',   [ReservationController::class, 'inHouse'])->name('in-house');
    Route::get('departures', [ReservationController::class, 'departures'])->name('departures');
});
```

- [ ] **Step 8.6: Commit**

```bash
git add app/Modules/FrontDesk/Services/ReservationService.php \
        app/Modules/FrontDesk/Controllers/Api/V1/ReservationController.php \
        app/Modules/FrontDesk/Requests/StoreReservationRequest.php \
        app/Modules/FrontDesk/Resources/ReservationResource.php \
        routes/tenant-api.php
git commit -m "feat: add ReservationService, API controller, and routes for front desk"
```

---

## Task 9: Reverb Broadcast + ReservationObserver

**Files:**
- Create: `app/Events/ReservationStatusChanged.php`
- Create: `app/Modules/FrontDesk/Observers/ReservationObserver.php`
- Modify: `app/Providers/AppServiceProvider.php`

- [ ] **Step 9.1: Create broadcast event**

Create `app/Events/ReservationStatusChanged.php`:

```php
<?php

declare(strict_types=1);

namespace App\Events;

use App\Modules\FrontDesk\Models\Reservation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantModel;

class ReservationStatusChanged implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use UsesTenantModel;

    public function __construct(public readonly Reservation $reservation) {}

    public function broadcastOn(): Channel
    {
        $tenantId = $this->getTenantModel()::current()?->id ?? 0;
        return new Channel("tenant.{$tenantId}.tape-chart");
    }

    public function broadcastWith(): array
    {
        return [
            'reservation_id' => $this->reservation->id,
            'booking_ref'    => $this->reservation->booking_ref,
            'status'         => $this->reservation->status->value,
            'room_id'        => $this->reservation->room_id,
            'check_in_date'  => $this->reservation->check_in_date->toDateString(),
            'check_out_date' => $this->reservation->check_out_date->toDateString(),
        ];
    }
}
```

- [ ] **Step 9.2: Create ReservationObserver**

Create `app/Modules/FrontDesk/Observers/ReservationObserver.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Observers;

use App\Events\ReservationStatusChanged;
use App\Modules\FrontDesk\Models\Reservation;

class ReservationObserver
{
    public function updated(Reservation $reservation): void
    {
        if ($reservation->wasChanged('status')) {
            ReservationStatusChanged::dispatch($reservation);
        }
    }
}
```

- [ ] **Step 9.3: Register observer in AppServiceProvider**

In `app/Providers/AppServiceProvider.php`, inside `boot()`:

```php
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Observers\ReservationObserver;

Reservation::observe(ReservationObserver::class);
```

- [ ] **Step 9.4: Commit**

```bash
git add app/Events/ReservationStatusChanged.php \
        app/Modules/FrontDesk/Observers/ReservationObserver.php \
        app/Providers/AppServiceProvider.php
git commit -m "feat: broadcast ReservationStatusChanged via Reverb on status update"
```

---

## Task 10: Tape Chart Data Endpoint

**Files:**
- Create: `app/Modules/FrontDesk/Services/TapeChartService.php`
- Create: `app/Modules/FrontDesk/Controllers/Api/V1/TapeChartController.php`

- [ ] **Step 10.1: Create TapeChartService**

Create `app/Modules/FrontDesk/Services/TapeChartService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Services;

use App\Enums\ReservationStatus;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Models\Room;
use App\Modules\FrontDesk\Models\RoomBlock;
use Carbon\Carbon;
use Illuminate\Support\Collection;

readonly class TapeChartService
{
    public function getData(int $propertyId, string $startDate, int $days = 14): array
    {
        $start = Carbon::parse($startDate);
        $end   = $start->copy()->addDays($days);

        $rooms = Room::where('property_id', $propertyId)
            ->with('roomType')
            ->orderBy('room_type_id')
            ->orderBy('room_number')
            ->get();

        $reservations = Reservation::where('property_id', $propertyId)
            ->whereNotIn('status', [ReservationStatus::Cancelled->value, ReservationStatus::NoShow->value])
            ->where('check_in_date', '<', $end->toDateString())
            ->where('check_out_date', '>', $start->toDateString())
            ->whereNotNull('room_id')
            ->get()
            ->groupBy('room_id');

        $blocks = RoomBlock::where('property_id', $propertyId)
            ->where('start_date', '<', $end->toDateString())
            ->where('end_date', '>', $start->toDateString())
            ->get()
            ->groupBy('room_id');

        return [
            'start_date' => $start->toDateString(),
            'days'       => $days,
            'rooms'      => $rooms->map(fn (Room $room) => [
                'id'          => $room->id,
                'number'      => $room->room_number,
                'type'        => $room->roomType?->name,
                'floor'       => $room->floor,
                'hk_status'   => $room->hk_status,
                'status'      => $room->status,
                'reservations'=> ($reservations[$room->id] ?? collect())->map(fn ($r) => [
                    'id'             => $r->id,
                    'booking_ref'    => $r->booking_ref,
                    'status'         => $r->status->value,
                    'check_in_date'  => $r->check_in_date->toDateString(),
                    'check_out_date' => $r->check_out_date->toDateString(),
                    'nights'         => $r->nights(),
                ])->values(),
                'blocks'      => ($blocks[$room->id] ?? collect())->map(fn ($b) => [
                    'id'         => $b->id,
                    'block_type' => $b->block_type,
                    'start_date' => $b->start_date->toDateString(),
                    'end_date'   => $b->end_date->toDateString(),
                    'reason'     => $b->reason,
                ])->values(),
            ])->values(),
        ];
    }
}
```

- [ ] **Step 10.2: Create TapeChartController**

Create `app/Modules/FrontDesk/Controllers/Api/V1/TapeChartController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Services\TapeChartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TapeChartController extends Controller
{
    public function __construct(private readonly TapeChartService $service) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'property_id' => ['required', 'integer'],
            'start_date'  => ['nullable', 'date'],
            'days'        => ['nullable', 'integer', 'min:7', 'max:31'],
        ]);

        $data = $this->service->getData(
            propertyId: $request->integer('property_id'),
            startDate:  $request->input('start_date', today()->toDateString()),
            days:       $request->integer('days', 14),
        );

        return response()->json(['status' => 1, 'data' => $data, 'message' => '']);
    }
}
```

- [ ] **Step 10.3: Add route in tenant-api.php**

Inside the `front-desk` prefix group:

```php
Route::get('tape-chart', [TapeChartController::class, 'index'])->name('tape-chart');
```

- [ ] **Step 10.4: Commit**

```bash
git add app/Modules/FrontDesk/Services/TapeChartService.php \
        app/Modules/FrontDesk/Controllers/Api/V1/TapeChartController.php \
        routes/tenant-api.php
git commit -m "feat: add Tape Chart data endpoint with reservations and blocks overlay"
```

---

## Task 11: Room Blocks CRUD

**Files:**
- Create: `app/Modules/FrontDesk/Controllers/Api/V1/RoomBlockController.php`
- Create: `app/Modules/FrontDesk/Requests/StoreRoomBlockRequest.php`
- Test: `tests/Feature/FrontDesk/RoomBlockTest.php`

- [ ] **Step 11.1: Write failing test**

Create `tests/Feature/FrontDesk/RoomBlockTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\FrontDesk\Models\RoomBlock;

it('creates a room block via API', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/front-desk/room-blocks', [
            'room_id'    => 1,
            'property_id'=> 1,
            'block_type' => 'out_of_order',
            'start_date' => '2026-10-01',
            'end_date'   => '2026-10-05',
            'reason'     => 'Renovation',
        ])
        ->assertStatus(201)
        ->assertJsonPath('status', 1);

    expect(RoomBlock::where('room_id', 1)->exists())->toBeTrue();
});
```

- [ ] **Step 11.2: Run test to verify it fails**

```bash
php artisan test tests/Feature/FrontDesk/RoomBlockTest.php
```

- [ ] **Step 11.3: Create RoomBlockController**

Create `app/Modules/FrontDesk/Controllers/Api/V1/RoomBlockController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Models\RoomBlock;
use App\Modules\FrontDesk\Requests\StoreRoomBlockRequest;
use Illuminate\Http\JsonResponse;

class RoomBlockController extends Controller
{
    public function index(): JsonResponse
    {
        $blocks = RoomBlock::with('room')->orderBy('start_date')->paginate(20);
        return response()->json(['status' => 1, 'data' => $blocks, 'message' => '']);
    }

    public function store(StoreRoomBlockRequest $request): JsonResponse
    {
        $block = RoomBlock::create(array_merge($request->validated(), ['created_by' => auth()->id()]));
        return response()->json(['status' => 1, 'data' => $block, 'message' => 'Room blocked.'], 201);
    }

    public function destroy(RoomBlock $roomBlock): JsonResponse
    {
        $roomBlock->delete();
        return response()->json(['status' => 1, 'data' => null, 'message' => 'Block removed.']);
    }
}
```

Create `app/Modules/FrontDesk/Requests/StoreRoomBlockRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomBlockRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'room_id'     => ['required', 'integer', 'exists:rooms,id'],
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'block_type'  => ['required', 'in:out_of_order,out_of_service,maintenance'],
            'start_date'  => ['required', 'date'],
            'end_date'    => ['required', 'date', 'after:start_date'],
            'reason'      => ['nullable', 'string', 'max:500'],
        ];
    }
}
```

- [ ] **Step 11.4: Add route**

In `routes/tenant-api.php`, inside `front-desk` group:

```php
Route::apiResource('room-blocks', RoomBlockController::class)->only(['index', 'store', 'destroy']);
```

- [ ] **Step 11.5: Run test to verify it passes**

```bash
php artisan test tests/Feature/FrontDesk/RoomBlockTest.php
```

Expected: PASS.

- [ ] **Step 11.6: Commit**

```bash
git add app/Modules/FrontDesk/Controllers/Api/V1/RoomBlockController.php \
        app/Modules/FrontDesk/Requests/StoreRoomBlockRequest.php \
        tests/Feature/FrontDesk/RoomBlockTest.php
git commit -m "feat: add room block CRUD API"
```

---

## Task 12: TypeScript Types, Mapper, Pinia Store, Composable

**Files:**
- Create: `resources/js/Types/FrontDesk/reservation.ts`
- Create: `resources/js/Utils/Mappers/reservation.ts`
- Create: `resources/js/Stores/FrontDesk/reservationStore.ts`
- Create: `resources/js/Composables/FrontDesk/useReservations.ts`

- [ ] **Step 12.1: Create TypeScript types**

Create `resources/js/Types/FrontDesk/reservation.ts`:

```typescript
export type ReservationStatus =
  | 'confirmed'
  | 'waiting_confirmation'
  | 'checked_in'
  | 'checked_out'
  | 'cancelled'
  | 'no_show'

export interface ReservationFolio {
  id: number
  status: string
  balance_due: string
}

export interface Reservation {
  id: string
  booking_ref: string
  status: ReservationStatus
  status_label: string
  check_in_date: string
  check_out_date: string
  nights: number
  adults: number
  children: number
  base_rate: string
  total_amount: string
  source: string
  market_segment: string | null
  special_requests: string | null
  checked_in_at: string | null
  checked_out_at: string | null
  folio?: ReservationFolio
  created_at: string
}

export interface ReservationForm {
  property_id: number | null
  room_type_id: number | null
  room_id: number | null
  rate_plan_id: number | null
  guest_id: number | null
  check_in_date: string
  check_out_date: string
  adults: number
  children: number
  children_ages: number[]
  base_rate: number | null
  source: string
  market_segment: string
  special_requests: string
}

export interface TapeChartRoom {
  id: number
  number: string
  type: string
  floor: string | number
  hk_status: string
  status: string
  reservations: Array<{
    id: string
    booking_ref: string
    status: ReservationStatus
    check_in_date: string
    check_out_date: string
    nights: number
  }>
  blocks: Array<{
    id: number
    block_type: string
    start_date: string
    end_date: string
    reason: string | null
  }>
}

export interface TapeChartData {
  start_date: string
  days: number
  rooms: TapeChartRoom[]
}
```

- [ ] **Step 12.2: Create mapper**

Create `resources/js/Utils/Mappers/reservation.ts`:

```typescript
import type { Reservation } from '@/Types/FrontDesk/reservation'

export function mapReservation(raw: Record<string, unknown>): Reservation {
  return {
    id:               raw.id as string,
    booking_ref:      raw.booking_ref as string,
    status:           raw.status as Reservation['status'],
    status_label:     raw.status_label as string,
    check_in_date:    raw.check_in_date as string,
    check_out_date:   raw.check_out_date as string,
    nights:           raw.nights as number,
    adults:           raw.adults as number,
    children:         raw.children as number,
    base_rate:        raw.base_rate as string,
    total_amount:     raw.total_amount as string,
    source:           raw.source as string,
    market_segment:   raw.market_segment as string | null,
    special_requests: raw.special_requests as string | null,
    checked_in_at:    raw.checked_in_at as string | null,
    checked_out_at:   raw.checked_out_at as string | null,
    folio:            raw.folio as Reservation['folio'],
    created_at:       raw.created_at as string,
  }
}
```

- [ ] **Step 12.3: Create Pinia store**

Create `resources/js/Stores/FrontDesk/reservationStore.ts`:

```typescript
import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiClient from '@/Services/apiClient'
import { mapReservation } from '@/Utils/Mappers/reservation'
import type { Reservation, TapeChartData } from '@/Types/FrontDesk/reservation'

export const useReservationStore = defineStore('reservation', () => {
  const reservations   = ref<Reservation[]>([])
  const current        = ref<Reservation | null>(null)
  const tapeChart      = ref<TapeChartData | null>(null)
  const loading        = ref(false)
  const loadingList    = ref(false)
  const loadingDetail  = ref(false)
  const meta           = ref<{ current_page: number; last_page: number; total: number } | null>(null)

  async function fetchAll(params: Record<string, unknown> = {}, forceRefresh = false): Promise<void> {
    if (reservations.value.length && !forceRefresh) return
    loadingList.value = true
    try {
      const res = await apiClient.get('/api/v1/front-desk/reservations', { params })
      reservations.value = res.data.data.data.map(mapReservation)
      meta.value = res.data.data.meta
    } finally {
      loadingList.value = false
    }
  }

  async function fetchOne(id: string): Promise<void> {
    loadingDetail.value = true
    try {
      const res = await apiClient.get(`/api/v1/front-desk/reservations/${id}`)
      current.value = mapReservation(res.data.data)
    } finally {
      loadingDetail.value = false
    }
  }

  async function create(data: Record<string, unknown>): Promise<Reservation> {
    loading.value = true
    try {
      const res = await apiClient.post('/api/v1/front-desk/reservations', data)
      const reservation = mapReservation(res.data.data)
      reservations.value.unshift(reservation)
      return reservation
    } finally {
      loading.value = false
    }
  }

  async function performAction(id: string, action: 'check-in' | 'check-out' | 'cancel', payload: Record<string, unknown> = {}): Promise<void> {
    loading.value = true
    try {
      const res = await apiClient.post(`/api/v1/front-desk/reservations/${id}/${action}`, payload)
      const updated = mapReservation(res.data.data)
      const idx = reservations.value.findIndex(r => r.id === id)
      if (idx !== -1) reservations.value[idx] = updated
      if (current.value?.id === id) current.value = updated
    } finally {
      loading.value = false
    }
  }

  async function fetchTapeChart(propertyId: number, startDate: string, days = 14): Promise<void> {
    loading.value = true
    try {
      const res = await apiClient.get('/api/v1/front-desk/tape-chart', { params: { property_id: propertyId, start_date: startDate, days } })
      tapeChart.value = res.data.data
    } finally {
      loading.value = false
    }
  }

  return { reservations, current, tapeChart, loading, loadingList, loadingDetail, meta, fetchAll, fetchOne, create, performAction, fetchTapeChart }
})
```

- [ ] **Step 12.4: Create composable**

Create `resources/js/Composables/FrontDesk/useReservations.ts`:

```typescript
import { storeToRefs } from 'pinia'
import { useReservationStore } from '@/Stores/FrontDesk/reservationStore'

export function useReservations() {
  const store = useReservationStore()
  const { reservations, current, tapeChart, loading, loadingList, loadingDetail, meta } = storeToRefs(store)

  return {
    reservations,
    current,
    tapeChart,
    loading,
    loadingList,
    loadingDetail,
    meta,
    fetchAll:       store.fetchAll,
    fetchOne:       store.fetchOne,
    create:         store.create,
    performAction:  store.performAction,
    fetchTapeChart: store.fetchTapeChart,
  }
}
```

- [ ] **Step 12.5: Commit**

```bash
git add resources/js/Types/FrontDesk/reservation.ts \
        resources/js/Utils/Mappers/reservation.ts \
        resources/js/Stores/FrontDesk/reservationStore.ts \
        resources/js/Composables/FrontDesk/useReservations.ts
git commit -m "feat: add reservation TypeScript types, mapper, Pinia store, composable"
```

---

## Task 13: Vue Pages

**Files:**
- Create: `resources/js/Pages/FrontDesk/Reservation/Index.vue`
- Create: `resources/js/Pages/FrontDesk/Reservation/Create.vue`
- Create: `resources/js/Pages/FrontDesk/Reservation/Show.vue`
- Create: `resources/js/Pages/FrontDesk/Arrivals/Index.vue`
- Create: `resources/js/Pages/FrontDesk/InHouse/Index.vue`
- Create: `resources/js/Pages/FrontDesk/Departures/Index.vue`
- Create: `resources/js/Pages/FrontDesk/RoomBlocks/Index.vue`
- Modify: `resources/js/Pages/FrontDesk/Web/ReservationController.php`

- [ ] **Step 13.1: Create Web ReservationController**

Create `app/Modules/FrontDesk/Controllers/Web/ReservationController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('FrontDesk/Reservation/Index');
    }

    public function create(): Response
    {
        return Inertia::render('FrontDesk/Reservation/Create');
    }

    public function show(string $id): Response
    {
        return Inertia::render('FrontDesk/Reservation/Show', ['reservationId' => $id]);
    }

    public function arrivals(): Response
    {
        return Inertia::render('FrontDesk/Arrivals/Index');
    }

    public function inHouse(): Response
    {
        return Inertia::render('FrontDesk/InHouse/Index');
    }

    public function departures(): Response
    {
        return Inertia::render('FrontDesk/Departures/Index');
    }
}
```

- [ ] **Step 13.2: Create Reservation Index page (Tape Chart view)**

Create `resources/js/Pages/FrontDesk/Reservation/Index.vue`:

```vue
<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useReservations } from '@/Composables/FrontDesk/useReservations'
import { router } from '@inertiajs/vue3'

const { tapeChart, loading, fetchTapeChart } = useReservations()
const startDate = ref(new Date().toISOString().split('T')[0])

onMounted(() => fetchTapeChart(1, startDate.value))

function navigate(days: number) {
  const d = new Date(startDate.value)
  d.setDate(d.getDate() + days)
  startDate.value = d.toISOString().split('T')[0]
  fetchTapeChart(1, startDate.value)
}
</script>

<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold">Stay View — Tape Chart</h1>
      <div class="flex gap-2">
        <button class="btn-secondary" @click="navigate(-14)">← Prev</button>
        <span class="px-4 py-2 bg-white border rounded text-sm">{{ startDate }}</span>
        <button class="btn-secondary" @click="navigate(14)">Next →</button>
        <button class="btn-primary" @click="router.visit('/reservations/create')">+ New Booking</button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-10 text-gray-500">Loading tape chart…</div>

    <div v-else-if="tapeChart" class="overflow-x-auto">
      <div class="min-w-[900px]">
        <!-- Header: dates -->
        <div class="flex border-b bg-gray-50">
          <div class="w-32 shrink-0 px-3 py-2 font-medium text-xs text-gray-600">Room</div>
          <div
            v-for="n in tapeChart.days"
            :key="n"
            class="flex-1 text-center text-xs py-2 border-l"
          >
            {{ new Date(new Date(tapeChart.start_date).setDate(new Date(tapeChart.start_date).getDate() + n - 1)).toLocaleDateString('en', { month: 'short', day: 'numeric' }) }}
          </div>
        </div>
        <!-- Rows: rooms -->
        <div
          v-for="room in tapeChart.rooms"
          :key="room.id"
          class="flex border-b hover:bg-gray-50"
        >
          <div class="w-32 shrink-0 px-3 py-2 text-sm">
            <div class="font-medium">{{ room.number }}</div>
            <div class="text-xs text-gray-500">{{ room.type }}</div>
          </div>
          <div class="flex-1 relative" :style="{ display: 'grid', gridTemplateColumns: `repeat(${tapeChart.days}, 1fr)` }">
            <div
              v-for="n in tapeChart.days"
              :key="n"
              class="border-l h-full min-h-[40px]"
            />
            <!-- Reservation bars -->
            <div
              v-for="res in room.reservations"
              :key="res.id"
              class="absolute top-1 h-[32px] rounded text-xs flex items-center px-2 cursor-pointer overflow-hidden"
              :class="{
                'bg-blue-500 text-white': res.status === 'confirmed',
                'bg-green-500 text-white': res.status === 'checked_in',
                'bg-gray-400 text-white': res.status === 'checked_out',
              }"
              :style="getTapeBarStyle(res, tapeChart.start_date, tapeChart.days)"
              @click="router.visit(`/reservations/${res.id}`)"
            >
              {{ res.booking_ref }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
function getTapeBarStyle(res: { check_in_date: string; check_out_date: string; nights: number }, startDate: string, totalDays: number) {
  const start = new Date(startDate)
  const checkIn = new Date(res.check_in_date)
  const checkOut = new Date(res.check_out_date)

  const offsetDays = Math.max(0, Math.floor((checkIn.getTime() - start.getTime()) / 86400000))
  const spanDays   = Math.min(totalDays - offsetDays, Math.floor((checkOut.getTime() - checkIn.getTime()) / 86400000))

  const left  = (offsetDays / totalDays) * 100
  const width = (spanDays  / totalDays) * 100

  return { left: `${left}%`, width: `${width}%` }
}
</script>
```

- [ ] **Step 13.3: Create Arrivals page**

Create `resources/js/Pages/FrontDesk/Arrivals/Index.vue`:

```vue
<script setup lang="ts">
import { onMounted, ref } from 'vue'
import apiClient from '@/Services/apiClient'
import { mapReservation } from '@/Utils/Mappers/reservation'
import type { Reservation } from '@/Types/FrontDesk/reservation'
import { router } from '@inertiajs/vue3'

const arrivals = ref<Reservation[]>([])
const loading  = ref(false)

onMounted(async () => {
  loading.value = true
  const res = await apiClient.get('/api/v1/front-desk/arrivals', { params: { property_id: 1 } })
  arrivals.value = res.data.data.map(mapReservation)
  loading.value = false
})

async function checkIn(id: string) {
  await apiClient.post(`/api/v1/front-desk/reservations/${id}/check-in`)
  arrivals.value = arrivals.value.filter(r => r.id !== id)
}
</script>

<template>
  <div class="p-6">
    <h1 class="text-xl font-semibold mb-4">Today's Arrivals</h1>
    <div v-if="loading" class="text-gray-500">Loading…</div>
    <table v-else class="w-full text-sm">
      <thead>
        <tr class="bg-gray-50 border-b">
          <th class="px-4 py-2 text-left">Booking Ref</th>
          <th class="px-4 py-2 text-left">Guest</th>
          <th class="px-4 py-2 text-left">Room</th>
          <th class="px-4 py-2 text-left">Nights</th>
          <th class="px-4 py-2 text-left">Status</th>
          <th class="px-4 py-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="r in arrivals" :key="r.id" class="border-b hover:bg-gray-50">
          <td class="px-4 py-2 font-mono">{{ r.booking_ref }}</td>
          <td class="px-4 py-2">{{ r.adults }} Adults</td>
          <td class="px-4 py-2">—</td>
          <td class="px-4 py-2">{{ r.nights }}</td>
          <td class="px-4 py-2">
            <span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-700">{{ r.status_label }}</span>
          </td>
          <td class="px-4 py-2 flex gap-2">
            <button class="btn-primary text-xs" @click="checkIn(r.id)">Check In</button>
            <button class="btn-secondary text-xs" @click="router.visit(`/reservations/${r.id}`)">View</button>
          </td>
        </tr>
        <tr v-if="!arrivals.length">
          <td colspan="6" class="text-center py-8 text-gray-400">No arrivals today.</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
```

- [ ] **Step 13.4: Register web routes**

In `routes/tenant.php`, inside the `auth.token` group:

```php
Route::prefix('front-desk')->name('front-desk.')->group(function (): void {
    Route::get('/stay-view',   [FrontDeskWebController::class, 'index'])->name('stay-view');
    Route::get('/reservations/create', [FrontDeskWebController::class, 'create'])->name('reservations.create');
    Route::get('/reservations/{id}',   [FrontDeskWebController::class, 'show'])->name('reservations.show');
    Route::get('/arrivals',    [FrontDeskWebController::class, 'arrivals'])->name('arrivals');
    Route::get('/in-house',    [FrontDeskWebController::class, 'inHouse'])->name('in-house');
    Route::get('/departures',  [FrontDeskWebController::class, 'departures'])->name('departures');
});
```

- [ ] **Step 13.5: Run full test suite**

```bash
composer run test
```

Expected: all tests pass.

- [ ] **Step 13.6: Final commit**

```bash
git add resources/js/Pages/FrontDesk/ \
        app/Modules/FrontDesk/Controllers/Web/ReservationController.php \
        routes/tenant.php
git commit -m "feat: add Tape Chart, Arrivals, InHouse, Departures Vue pages and web routes"
```

---

## Phase 3 Completion Checklist

- [ ] `php artisan test tests/Feature/FrontDesk/` → all PASS
- [ ] `POST /api/v1/front-desk/reservations` → creates reservation + folio atomically
- [ ] Double-booking attempt on same room/dates → 422 "Room not available"
- [ ] Check-in sets `status=checked_in`, room `status=occupied`, folio `status=active`
- [ ] Check-out sets `status=checked_out`, room `status=vacant`, `hk_status=dirty`
- [ ] Cancel sets `status=cancelled`, releases room inventory
- [ ] `GET /api/v1/front-desk/tape-chart` → returns rooms with reservation bars
- [ ] Reservation status change broadcasts to `tenant.{id}.tape-chart` channel
- [ ] `composer run test` → all tests PASS
- [ ] `./vendor/bin/pint` → no violations
