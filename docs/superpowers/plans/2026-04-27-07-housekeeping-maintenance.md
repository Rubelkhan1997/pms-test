# Housekeeping & Maintenance Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the Housekeeping board, HK assignment system, Maintenance request tracker, and Laundry workflow. Room status changes are triggered automatically by reservation events via Observers, with real-time board updates via Reverb.

**Architecture:** Checkout event sets `rooms.hk_status = dirty` via `ReservationObserver` (wired in Phase 3). HK completing a room broadcasts `tenant.{id}.housekeeping` event. Maintenance blocks create `room_blocks` records (shared table from Phase 3).

**Tech Stack:** Laravel 13, Laravel Reverb, Pest PHP.

**Depends on:** Phase 3 (Reservation Engine — room_blocks table, rooms.hk_status column, ReservationObserver checkout trigger).

---

## File Map

| Action | File | Responsibility |
|---|---|---|
| Create | `database/migrations/tenant/2026_04_27_500000_create_hk_assignments_table.php` | Room → housekeeper per date |
| Create | `database/migrations/tenant/2026_04_27_500100_create_maintenance_requests_table.php` | Maintenance tickets |
| Create | `database/migrations/tenant/2026_04_27_500200_create_laundry_items_table.php` | Linen tracking |
| Create | `app/Enums/HkStatus.php` | dirty, hk_assigned, clean, blocked |
| Create | `app/Enums/MaintenanceStatus.php` | open, in_progress, resolved, closed |
| Create | `app/Modules/Housekeeping/Models/HkAssignment.php` | Assignment model |
| Create | `app/Modules/Housekeeping/Models/MaintenanceRequest.php` | Maintenance ticket model |
| Create | `app/Modules/Housekeeping/Models/LaundryItem.php` | Laundry item model |
| Create | `app/Modules/Housekeeping/Actions/AssignRoomAction.php` | Assign room to housekeeper |
| Create | `app/Modules/Housekeeping/Actions/UpdateRoomHkStatusAction.php` | HK status update + broadcast |
| Create | `app/Modules/Housekeeping/Actions/AutoAssignDirtyRoomsAction.php` | Auto-generate assignments on checkout |
| Create | `app/Events/RoomHkStatusChanged.php` | Reverb broadcast event |
| Create | `app/Modules/Housekeeping/Services/HousekeepingService.php` | Orchestrator |
| Create | `app/Modules/Housekeeping/Controllers/Api/V1/HousekeepingController.php` | HK board + assignment API |
| Create | `app/Modules/Housekeeping/Controllers/Api/V1/MaintenanceController.php` | Maintenance CRUD API |
| Create | `app/Modules/Housekeeping/Controllers/Api/V1/LaundryController.php` | Laundry CRUD API |
| Create | `app/Modules/Housekeeping/Controllers/Web/HousekeepingController.php` | Inertia pages |
| Create | `app/Modules/Housekeeping/Requests/StoreMaintenanceRequest.php` | |
| Create | `app/Modules/Housekeeping/Resources/HkBoardResource.php` | |
| Create | `resources/js/Types/Housekeeping/housekeeping.ts` | TS types |
| Create | `resources/js/Stores/Housekeeping/housekeepingStore.ts` | Pinia store |
| Create | `resources/js/Composables/Housekeeping/useHousekeeping.ts` | Composable |
| Create | `resources/js/Pages/Housekeeping/HouseStatus/Index.vue` | HK board |
| Create | `resources/js/Pages/Housekeeping/Maintenance/Index.vue` | Maintenance list |
| Create | `resources/js/Pages/Housekeeping/Laundry/Index.vue` | Laundry tracking |
| Create | `tests/Feature/Housekeeping/HkAssignmentTest.php` | Assignment tests |
| Create | `tests/Feature/Housekeeping/MaintenanceTest.php` | Maintenance CRUD tests |
| Create | `tests/Feature/Housekeeping/LaundryTest.php` | Laundry status machine tests |

---

## Task 1: Database Migrations + Enums

- [ ] **Step 1.1: Create HkStatus and MaintenanceStatus enums**

Create `app/Enums/HkStatus.php`:

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum HkStatus: string
{
    case Dirty      = 'dirty';
    case HkAssigned = 'hk_assigned';
    case Clean      = 'clean';
    case Blocked    = 'blocked';
    case Inspected  = 'inspected';

    public function label(): string
    {
        return match($this) {
            self::Dirty      => 'Dirty',
            self::HkAssigned => 'HK Assigned',
            self::Clean      => 'Clean',
            self::Blocked    => 'Blocked',
            self::Inspected  => 'Inspected',
        };
    }
}
```

Create `app/Enums/MaintenanceStatus.php`:

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum MaintenanceStatus: string
{
    case Open       = 'open';
    case InProgress = 'in_progress';
    case Resolved   = 'resolved';
    case Closed     = 'closed';
}
```

- [ ] **Step 1.2: Create hk_assignments migration**

Create `database/migrations/tenant/2026_04_27_500000_create_hk_assignments_table.php`:

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
        Schema::create('hk_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();  // housekeeper
            $table->date('assignment_date');
            $table->string('hk_status')->default('dirty');   // HkStatus enum
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['property_id', 'assignment_date']);
            $table->index(['assigned_to', 'assignment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hk_assignments');
    }
};
```

- [ ] **Step 1.3: Add hk_status column to rooms table**

Create `database/migrations/tenant/2026_04_27_500001_add_hk_status_to_rooms_table.php`:

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
        Schema::table('rooms', function (Blueprint $table): void {
            $table->string('hk_status')->default('clean')->after('status');
            $table->index('hk_status');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            $table->dropColumn('hk_status');
        });
    }
};
```

- [ ] **Step 1.4: Create maintenance_requests migration**

Create `database/migrations/tenant/2026_04_27_500100_create_maintenance_requests_table.php`:

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
        Schema::create('maintenance_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('property_id')->constrained('properties');
            $table->string('category');                          // plumbing, electrical, hvac, furniture, etc.
            $table->string('priority')->default('normal');       // low, normal, high, urgent
            $table->string('status')->default('open');           // MaintenanceStatus enum
            $table->text('description');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['property_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
```

- [ ] **Step 1.5: Create laundry_items migration**

Create `database/migrations/tenant/2026_04_27_500200_create_laundry_items_table.php`:

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
        Schema::create('laundry_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained('properties');
            $table->string('item_type');                         // bed_sheet, pillow_case, towel, etc.
            $table->integer('quantity')->default(1);
            $table->string('status')->default('collected');      // collected, in_laundry, cleaned, returned
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->date('collected_date');
            $table->timestamp('sent_to_laundry_at')->nullable();
            $table->timestamp('cleaned_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['property_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laundry_items');
    }
};
```

- [ ] **Step 1.6: Run migrations**

```bash
php artisan migrate --path=database/migrations/tenant --database=mysql
```

Expected: hk_assignments, maintenance_requests, laundry_items created; hk_status column added to rooms.

- [ ] **Step 1.7: Commit**

```bash
git add database/migrations/tenant/2026_04_27_500*.php app/Enums/HkStatus.php app/Enums/MaintenanceStatus.php
git commit -m "feat: add housekeeping migrations, HkStatus and MaintenanceStatus enums"
```

---

## Task 2: Models

- [ ] **Step 2.1: Create HkAssignment model**

Create `app/Modules/Housekeeping/Models/HkAssignment.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Models;

use App\Enums\HkStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HkAssignment extends Model
{
    protected $fillable = [
        'room_id', 'property_id', 'assigned_to', 'assignment_date',
        'hk_status', 'started_at', 'completed_at', 'notes', 'created_by',
    ];

    protected $casts = [
        'hk_status'    => HkStatus::class,
        'assignment_date' => 'date',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\FrontDesk\Models\Room::class);
    }
}
```

Create `app/Modules/Housekeeping/Models/MaintenanceRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Models;

use App\Enums\MaintenanceStatus;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    protected $fillable = [
        'room_id', 'property_id', 'category', 'priority', 'status',
        'description', 'assigned_to', 'resolved_at', 'resolution_notes', 'reported_by',
    ];

    protected $casts = [
        'status'      => MaintenanceStatus::class,
        'resolved_at' => 'datetime',
    ];
}
```

Create `app/Modules/Housekeeping/Models/LaundryItem.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Models;

use Illuminate\Database\Eloquent\Model;

class LaundryItem extends Model
{
    protected $fillable = [
        'property_id', 'item_type', 'quantity', 'status', 'room_id',
        'collected_date', 'sent_to_laundry_at', 'cleaned_at', 'returned_at', 'notes', 'handled_by',
    ];

    protected $casts = [
        'collected_date'    => 'date',
        'sent_to_laundry_at'=> 'datetime',
        'cleaned_at'        => 'datetime',
        'returned_at'       => 'datetime',
    ];

    public function advance(): void
    {
        $transitions = [
            'collected'   => ['status' => 'in_laundry',  'field' => 'sent_to_laundry_at'],
            'in_laundry'  => ['status' => 'cleaned',     'field' => 'cleaned_at'],
            'cleaned'     => ['status' => 'returned',    'field' => 'returned_at'],
        ];

        if (isset($transitions[$this->status])) {
            $t = $transitions[$this->status];
            $this->update(['status' => $t['status'], $t['field'] => now()]);
        }
    }
}
```

- [ ] **Step 2.2: Commit**

```bash
git add app/Modules/Housekeeping/Models/
git commit -m "feat: add HkAssignment, MaintenanceRequest, LaundryItem models"
```

---

## Task 3: HK Actions + Broadcast

- [ ] **Step 3.1: Create RoomHkStatusChanged event**

Create `app/Events/RoomHkStatusChanged.php`:

```php
<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantModel;

class RoomHkStatusChanged implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use UsesTenantModel;

    public function __construct(
        public readonly int    $roomId,
        public readonly string $roomNumber,
        public readonly string $hkStatus,
    ) {}

    public function broadcastOn(): Channel
    {
        $tenantId = $this->getTenantModel()::current()?->id ?? 0;
        return new Channel("tenant.{$tenantId}.housekeeping");
    }

    public function broadcastWith(): array
    {
        return [
            'room_id'     => $this->roomId,
            'room_number' => $this->roomNumber,
            'hk_status'   => $this->hkStatus,
        ];
    }
}
```

- [ ] **Step 3.2: Create UpdateRoomHkStatusAction**

Create `app/Modules/Housekeeping/Actions/UpdateRoomHkStatusAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Actions;

use App\Enums\HkStatus;
use App\Events\RoomHkStatusChanged;
use App\Modules\FrontDesk\Models\Room;
use App\Modules\Housekeeping\Models\HkAssignment;
use Illuminate\Support\Facades\DB;

class UpdateRoomHkStatusAction
{
    public function execute(int $roomId, HkStatus $newStatus, ?int $assignmentId = null): void
    {
        DB::transaction(function () use ($roomId, $newStatus, $assignmentId): void {
            $room = Room::findOrFail($roomId);
            $room->update(['hk_status' => $newStatus->value]);

            // Update assignment if linked
            if ($assignmentId) {
                $assignment = HkAssignment::find($assignmentId);
                if ($assignment) {
                    $updates = ['hk_status' => $newStatus];
                    if ($newStatus === HkStatus::Clean) {
                        $updates['completed_at'] = now();
                    } elseif ($newStatus === HkStatus::HkAssigned) {
                        $updates['started_at'] = now();
                    }
                    $assignment->update($updates);
                }
            }

            RoomHkStatusChanged::dispatch($roomId, $room->room_number, $newStatus->value);
        });
    }
}
```

- [ ] **Step 3.3: Create AssignRoomAction**

Create `app/Modules/Housekeeping/Actions/AssignRoomAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Actions;

use App\Enums\HkStatus;
use App\Modules\Housekeeping\Models\HkAssignment;
use Carbon\Carbon;

class AssignRoomAction
{
    public function __construct(private readonly UpdateRoomHkStatusAction $updateHkStatus) {}

    public function execute(int $roomId, int $propertyId, int $housekeeperId, Carbon $date): HkAssignment
    {
        $assignment = HkAssignment::updateOrCreate(
            ['room_id' => $roomId, 'assignment_date' => $date->toDateString()],
            ['property_id' => $propertyId, 'assigned_to' => $housekeeperId, 'hk_status' => HkStatus::HkAssigned],
        );

        $this->updateHkStatus->execute($roomId, HkStatus::HkAssigned, $assignment->id);

        return $assignment;
    }
}
```

- [ ] **Step 3.4: Create AutoAssignDirtyRoomsAction**

Create `app/Modules/Housekeeping/Actions/AutoAssignDirtyRoomsAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Actions;

use App\Enums\HkStatus;
use App\Modules\FrontDesk\Models\Room;
use App\Modules\Housekeeping\Models\HkAssignment;
use Carbon\Carbon;

class AutoAssignDirtyRoomsAction
{
    public function execute(int $propertyId, Carbon $date): int
    {
        $dirtyRooms = Room::where('property_id', $propertyId)
            ->where('hk_status', HkStatus::Dirty->value)
            ->get();

        foreach ($dirtyRooms as $room) {
            HkAssignment::firstOrCreate([
                'room_id'         => $room->id,
                'assignment_date' => $date->toDateString(),
            ], [
                'property_id' => $propertyId,
                'hk_status'   => HkStatus::Dirty,
            ]);
        }

        return $dirtyRooms->count();
    }
}
```

- [ ] **Step 3.5: Write and run failing test**

Create `tests/Feature/Housekeeping/HkAssignmentTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\HkStatus;
use App\Modules\FrontDesk\Models\Room;
use App\Modules\Housekeeping\Actions\UpdateRoomHkStatusAction;
use App\Events\RoomHkStatusChanged;
use Illuminate\Support\Facades\Event;

it('updates room hk_status and fires broadcast event', function (): void {
    Event::fake([RoomHkStatusChanged::class]);

    $room = Room::factory()->create(['hk_status' => 'dirty']);

    app(UpdateRoomHkStatusAction::class)->execute($room->id, HkStatus::Clean);

    expect($room->fresh()->hk_status)->toBe('clean');
    Event::assertDispatched(RoomHkStatusChanged::class, fn ($e) => $e->roomId === $room->id && $e->hkStatus === 'clean');
});
```

```bash
php artisan test tests/Feature/Housekeeping/HkAssignmentTest.php
```

Expected: PASS.

- [ ] **Step 3.6: Commit**

```bash
git add app/Events/RoomHkStatusChanged.php \
        app/Modules/Housekeeping/Actions/ \
        tests/Feature/Housekeeping/HkAssignmentTest.php
git commit -m "feat: add HK status update action with Reverb broadcast and auto-assign dirty rooms"
```

---

## Task 4: HousekeepingService + API Controllers + Routes

- [ ] **Step 4.1: Create HousekeepingService**

Create `app/Modules/Housekeeping/Services/HousekeepingService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Services;

use App\Enums\HkStatus;
use App\Modules\FrontDesk\Models\Room;
use App\Modules\Housekeeping\Actions\AssignRoomAction;
use App\Modules\Housekeeping\Actions\UpdateRoomHkStatusAction;
use App\Modules\Housekeeping\Models\HkAssignment;
use App\Modules\Housekeeping\Models\MaintenanceRequest;
use Carbon\Carbon;
use Illuminate\Support\Collection;

readonly class HousekeepingService
{
    public function __construct(
        private AssignRoomAction        $assign,
        private UpdateRoomHkStatusAction $updateStatus,
    ) {}

    public function getBoardForDate(int $propertyId, string $date): Collection
    {
        return Room::where('property_id', $propertyId)
            ->with(['roomType', 'hkAssignments' => fn ($q) => $q->whereDate('assignment_date', $date)])
            ->orderBy('room_number')
            ->get();
    }

    public function assignRoom(int $roomId, int $propertyId, int $housekeeperId, string $date): HkAssignment
    {
        return $this->assign->execute($roomId, $propertyId, $housekeeperId, Carbon::parse($date));
    }

    public function updateRoomHkStatus(int $roomId, string $status, ?int $assignmentId = null): void
    {
        $this->updateStatus->execute($roomId, HkStatus::from($status), $assignmentId);
    }

    public function getMaintenanceRequests(int $propertyId, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        return MaintenanceRequest::where('property_id', $propertyId)
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['priority'] ?? null, fn ($q, $p) => $q->where('priority', $p))
            ->latest()
            ->paginate(20);
    }
}
```

- [ ] **Step 4.2: Create HousekeepingController**

Create `app/Modules/Housekeeping/Controllers/Api/V1/HousekeepingController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Housekeeping\Services\HousekeepingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HousekeepingController extends Controller
{
    public function __construct(private readonly HousekeepingService $service) {}

    public function board(Request $request): JsonResponse
    {
        $request->validate(['property_id' => ['required', 'integer'], 'date' => ['nullable', 'date']]);
        $board = $this->service->getBoardForDate($request->integer('property_id'), $request->input('date', today()->toDateString()));
        return response()->json(['status' => 1, 'data' => $board, 'message' => '']);
    }

    public function assign(Request $request): JsonResponse
    {
        $request->validate([
            'room_id'       => ['required', 'integer', 'exists:rooms,id'],
            'property_id'   => ['required', 'integer'],
            'housekeeper_id'=> ['required', 'integer', 'exists:users,id'],
            'date'          => ['required', 'date'],
        ]);

        $assignment = $this->service->assignRoom(
            $request->integer('room_id'),
            $request->integer('property_id'),
            $request->integer('housekeeper_id'),
            $request->input('date'),
        );

        return response()->json(['status' => 1, 'data' => $assignment, 'message' => 'Room assigned.'], 201);
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'room_id'       => ['required', 'integer'],
            'hk_status'     => ['required', 'in:dirty,hk_assigned,clean,blocked,inspected'],
            'assignment_id' => ['nullable', 'integer'],
        ]);

        $this->service->updateRoomHkStatus(
            $request->integer('room_id'),
            $request->input('hk_status'),
            $request->integer('assignment_id'),
        );

        return response()->json(['status' => 1, 'data' => null, 'message' => 'HK status updated.']);
    }
}
```

- [ ] **Step 4.3: Create MaintenanceController**

Create `app/Modules/Housekeeping/Controllers/Api/V1/MaintenanceController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Housekeeping\Models\MaintenanceRequest;
use App\Modules\Housekeeping\Requests\StoreMaintenanceRequest;
use App\Modules\Housekeeping\Services\HousekeepingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function __construct(private readonly HousekeepingService $service) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->getMaintenanceRequests($request->integer('property_id'), $request->only('status', 'priority'));
        return response()->json(['status' => 1, 'data' => $data, 'message' => '']);
    }

    public function store(StoreMaintenanceRequest $request): JsonResponse
    {
        $req = MaintenanceRequest::create(array_merge($request->validated(), ['reported_by' => auth()->id()]));
        return response()->json(['status' => 1, 'data' => $req, 'message' => 'Maintenance request created.'], 201);
    }

    public function update(Request $request, MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        $request->validate([
            'status'           => ['nullable', 'in:open,in_progress,resolved,closed'],
            'assigned_to'      => ['nullable', 'integer', 'exists:users,id'],
            'resolution_notes' => ['nullable', 'string'],
        ]);

        $updates = $request->only('status', 'assigned_to', 'resolution_notes');
        if (($updates['status'] ?? null) === 'resolved') {
            $updates['resolved_at'] = now();
        }
        $maintenanceRequest->update($updates);

        return response()->json(['status' => 1, 'data' => $maintenanceRequest, 'message' => 'Updated.']);
    }
}
```

Create `app/Modules/Housekeeping/Requests/StoreMaintenanceRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'room_id'     => ['nullable', 'integer', 'exists:rooms,id'],
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'category'    => ['required', 'string'],
            'priority'    => ['required', 'in:low,normal,high,urgent'],
            'description' => ['required', 'string', 'max:2000'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
```

- [ ] **Step 4.4: Create LaundryController**

Create `app/Modules/Housekeeping/Controllers/Api/V1/LaundryController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Housekeeping\Models\LaundryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LaundryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = LaundryItem::where('property_id', $request->integer('property_id'))
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20);
        return response()->json(['status' => 1, 'data' => $items, 'message' => '']);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'property_id'    => ['required', 'integer', 'exists:properties,id'],
            'item_type'      => ['required', 'string'],
            'quantity'       => ['required', 'integer', 'min:1'],
            'room_id'        => ['nullable', 'integer', 'exists:rooms,id'],
            'collected_date' => ['required', 'date'],
        ]);

        $item = LaundryItem::create(array_merge($request->validated(), ['handled_by' => auth()->id()]));
        return response()->json(['status' => 1, 'data' => $item, 'message' => 'Logged.'], 201);
    }

    public function advance(LaundryItem $laundryItem): JsonResponse
    {
        $laundryItem->advance();
        return response()->json(['status' => 1, 'data' => $laundryItem->fresh(), 'message' => "Status: {$laundryItem->status}"]);
    }
}
```

- [ ] **Step 4.5: Register routes**

In `routes/tenant-api.php`:

```php
Route::prefix('housekeeping')->name('housekeeping.')->group(function (): void {
    Route::get('/board',          [HousekeepingController::class, 'board'])->name('board');
    Route::post('/assign',        [HousekeepingController::class, 'assign'])->name('assign');
    Route::post('/update-status', [HousekeepingController::class, 'updateStatus'])->name('update-status');
    Route::apiResource('maintenance', MaintenanceController::class)->except(['show', 'destroy']);
    Route::get('/laundry',           [LaundryController::class, 'index'])->name('laundry.index');
    Route::post('/laundry',          [LaundryController::class, 'store'])->name('laundry.store');
    Route::post('/laundry/{item}/advance', [LaundryController::class, 'advance'])->name('laundry.advance');
});
```

- [ ] **Step 4.6: Run maintenance test**

Create `tests/Feature/Housekeeping/MaintenanceTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\User;

it('creates a maintenance request via API', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/housekeeping/maintenance', [
            'property_id' => 1,
            'category'    => 'plumbing',
            'priority'    => 'high',
            'description' => 'Bathroom tap leaking',
        ])
        ->assertStatus(201)
        ->assertJsonPath('status', 1);
});
```

```bash
php artisan test tests/Feature/Housekeeping/MaintenanceTest.php
```

Expected: PASS.

- [ ] **Step 4.7: Commit**

```bash
git add app/Modules/Housekeeping/Services/ \
        app/Modules/Housekeeping/Controllers/ \
        app/Modules/Housekeeping/Requests/ \
        tests/Feature/Housekeeping/ \
        routes/tenant-api.php
git commit -m "feat: add HousekeepingService, board/assign/maintenance/laundry API controllers"
```

---

## Task 5: TypeScript Types, Store, Composable + Vue Pages

- [ ] **Step 5.1: Create TypeScript types**

Create `resources/js/Types/Housekeeping/housekeeping.ts`:

```typescript
export type HkStatus = 'dirty' | 'hk_assigned' | 'clean' | 'blocked' | 'inspected'
export type MaintenanceStatus = 'open' | 'in_progress' | 'resolved' | 'closed'
export type MaintenancePriority = 'low' | 'normal' | 'high' | 'urgent'

export interface HkBoardRoom {
  id: number
  room_number: string
  floor: string | number
  hk_status: HkStatus
  status: string
  room_type?: { name: string }
  hk_assignments: Array<{
    id: number
    assigned_to: number | null
    hk_status: HkStatus
    started_at: string | null
    completed_at: string | null
  }>
}

export interface MaintenanceRequest {
  id: number
  room_id: number | null
  property_id: number
  category: string
  priority: MaintenancePriority
  status: MaintenanceStatus
  description: string
  assigned_to: number | null
  resolved_at: string | null
  resolution_notes: string | null
  created_at: string
}

export interface LaundryItem {
  id: number
  item_type: string
  quantity: number
  status: 'collected' | 'in_laundry' | 'cleaned' | 'returned'
  room_id: number | null
  collected_date: string
  sent_to_laundry_at: string | null
  cleaned_at: string | null
  returned_at: string | null
}
```

- [ ] **Step 5.2: Create Pinia store**

Create `resources/js/Stores/Housekeeping/housekeepingStore.ts`:

```typescript
import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiClient from '@/Services/apiClient'
import type { HkBoardRoom, MaintenanceRequest, LaundryItem } from '@/Types/Housekeeping/housekeeping'

export const useHousekeepingStore = defineStore('housekeeping', () => {
  const board        = ref<HkBoardRoom[]>([])
  const maintenance  = ref<MaintenanceRequest[]>([])
  const laundry      = ref<LaundryItem[]>([])
  const loadingList  = ref(false)
  const loading      = ref(false)

  async function fetchBoard(propertyId: number, date: string): Promise<void> {
    loadingList.value = true
    try {
      const res = await apiClient.get('/api/v1/housekeeping/board', { params: { property_id: propertyId, date } })
      board.value = res.data.data
    } finally {
      loadingList.value = false
    }
  }

  async function updateStatus(roomId: number, hkStatus: string, assignmentId?: number): Promise<void> {
    loading.value = true
    try {
      await apiClient.post('/api/v1/housekeeping/update-status', { room_id: roomId, hk_status: hkStatus, assignment_id: assignmentId })
      const room = board.value.find(r => r.id === roomId)
      if (room) room.hk_status = hkStatus as HkBoardRoom['hk_status']
    } finally {
      loading.value = false
    }
  }

  async function fetchMaintenance(propertyId: number, filters: Record<string, unknown> = {}): Promise<void> {
    loadingList.value = true
    try {
      const res = await apiClient.get('/api/v1/housekeeping/maintenance', { params: { property_id: propertyId, ...filters } })
      maintenance.value = res.data.data.data
    } finally {
      loadingList.value = false
    }
  }

  return { board, maintenance, laundry, loadingList, loading, fetchBoard, updateStatus, fetchMaintenance }
})
```

- [ ] **Step 5.3: Create composable**

Create `resources/js/Composables/Housekeeping/useHousekeeping.ts`:

```typescript
import { storeToRefs } from 'pinia'
import { useHousekeepingStore } from '@/Stores/Housekeeping/housekeepingStore'

export function useHousekeeping() {
  const store = useHousekeepingStore()
  const { board, maintenance, laundry, loadingList, loading } = storeToRefs(store)
  return { board, maintenance, laundry, loadingList, loading, ...store }
}
```

- [ ] **Step 5.4: Create House Status board page**

Create `resources/js/Pages/Housekeeping/HouseStatus/Index.vue`:

```vue
<script setup lang="ts">
import { onMounted } from 'vue'
import { useHousekeeping } from '@/Composables/Housekeeping/useHousekeeping'

const { board, loadingList, fetchBoard, updateStatus } = useHousekeeping()

onMounted(() => fetchBoard(1, new Date().toISOString().split('T')[0]))

const statusColors: Record<string, string> = {
  dirty:       'bg-red-100 text-red-700',
  hk_assigned: 'bg-yellow-100 text-yellow-700',
  clean:       'bg-green-100 text-green-700',
  blocked:     'bg-gray-100 text-gray-600',
  inspected:   'bg-blue-100 text-blue-700',
}

const nextStatus: Record<string, string> = {
  dirty: 'hk_assigned',
  hk_assigned: 'clean',
  clean: 'inspected',
}
</script>

<template>
  <div class="p-6">
    <h1 class="text-xl font-semibold mb-4">House Status Board</h1>
    <div v-if="loadingList" class="text-gray-500 text-center py-8">Loading…</div>
    <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
      <div
        v-for="room in board"
        :key="room.id"
        class="border rounded-lg p-3 flex flex-col gap-1"
        :class="statusColors[room.hk_status] ?? 'bg-white'"
      >
        <div class="font-semibold text-sm">{{ room.room_number }}</div>
        <div class="text-xs text-gray-500">{{ room.room_type?.name }}</div>
        <div class="text-xs font-medium capitalize">{{ room.hk_status.replace('_', ' ') }}</div>
        <button
          v-if="nextStatus[room.hk_status]"
          class="text-xs mt-1 px-2 py-0.5 rounded border border-current hover:opacity-80"
          @click="updateStatus(room.id, nextStatus[room.hk_status])"
        >
          Mark {{ nextStatus[room.hk_status].replace('_', ' ') }}
        </button>
      </div>
    </div>
  </div>
</template>
```

- [ ] **Step 5.5: Register web routes and Web Controller**

Create `app/Modules/Housekeeping/Controllers/Web/HousekeepingController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HousekeepingController extends Controller
{
    public function houseStatus(): Response  { return Inertia::render('Housekeeping/HouseStatus/Index'); }
    public function maintenance(): Response   { return Inertia::render('Housekeeping/Maintenance/Index'); }
    public function laundry(): Response       { return Inertia::render('Housekeeping/Laundry/Index'); }
}
```

In `routes/tenant.php`:

```php
Route::prefix('housekeeping')->name('housekeeping.')->group(function (): void {
    Route::get('/house-status', [\App\Modules\Housekeeping\Controllers\Web\HousekeepingController::class, 'houseStatus'])->name('house-status');
    Route::get('/maintenance',  [\App\Modules\Housekeeping\Controllers\Web\HousekeepingController::class, 'maintenance'])->name('maintenance');
    Route::get('/laundry',      [\App\Modules\Housekeeping\Controllers\Web\HousekeepingController::class, 'laundry'])->name('laundry');
});
```

- [ ] **Step 5.6: Run full test suite**

```bash
composer run test
```

Expected: all tests pass.

- [ ] **Step 5.7: Final commit**

```bash
git add resources/js/Types/Housekeeping/ resources/js/Stores/Housekeeping/ \
        resources/js/Composables/Housekeeping/ resources/js/Pages/Housekeeping/ \
        app/Modules/Housekeeping/Controllers/Web/ routes/tenant.php
git commit -m "feat: add Housekeeping Vue pages (board, maintenance, laundry), store, composable"
```

---

## Phase 6 Completion Checklist

- [ ] Checkout of a reservation sets `rooms.hk_status = dirty` (via ReservationObserver from Phase 3)
- [ ] `POST /api/v1/housekeeping/update-status` with `hk_status=clean` broadcasts to `tenant.{id}.housekeeping`
- [ ] House Status board shows all rooms with current HK status, color-coded
- [ ] Maintenance request CRUD works; `status=resolved` sets `resolved_at` timestamp
- [ ] Laundry item `advance()` cycles: collected → in_laundry → cleaned → returned
- [ ] `composer run test` → all PASS
- [ ] `./vendor/bin/pint` → no violations
