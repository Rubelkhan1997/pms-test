# Housekeeping & POS Module Implementation Guide

**Date:** March 17, 2026  
**Status:** ⏳ Ready for Implementation

---

## 📋 What Needs to Be Implemented

### 1. Housekeeping Module

#### Service Layer (`app/Modules/Housekeeping/Services/HousekeepingService.php`)
```php
<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Services;

use App\Base\BaseService;
use App\Modules\Housekeeping\Models\HousekeepingTask;
use App\Modules\Housekeeping\Enums\HousekeepingStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

readonly class HousekeepingService extends BaseService
{
    public function __construct(
        private HousekeepingTask $model
    ) {
        parent::setModel($model);
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->model->query()
            ->with(['room', 'hotel', 'createdBy', 'assignedTo'])
            ->applyFilters($filters)
            ->latest('scheduled_at')
            ->paginate(15);
    }

    public function create(array $payload): HousekeepingTask
    {
        $payload['reference'] = $this->generateReference();
        $payload['status'] = $payload['status'] ?? HousekeepingStatus::Pending->value;
        
        return $this->model->create($payload);
    }

    public function updateStatus(int $id, HousekeepingStatus $status): HousekeepingTask
    {
        $task = $this->findOrFail($id);
        $task->update(['status' => $status->value]);
        
        if ($status === HousekeepingStatus::Completed) {
            $task->update(['completed_at' => now()]);
        }
        
        return $task->fresh();
    }

    public function getByRoom(int $roomId): Collection
    {
        return $this->model->where('room_id', $roomId)
            ->latest('scheduled_at')
            ->get();
    }

    public function getPendingTasks(int $hotelId): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->where('status', HousekeepingStatus::Pending->value)
            ->latest('scheduled_at')
            ->get();
    }

    public function getTodayTasks(int $hotelId): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->whereDate('scheduled_at', today())
            ->latest('scheduled_at')
            ->get();
    }

    private function generateReference(): string
    {
        return 'HK-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
```

#### Web Controller (`app/Modules/Housekeeping/Controllers/Web/HousekeepingTaskController.php`)
```php
<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Housekeeping\Requests\StoreHousekeepingTaskRequest;
use App\Modules\Housekeeping\Requests\UpdateHousekeepingTaskRequest;
use App\Modules\Housekeeping\Services\HousekeepingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HousekeepingTaskController extends Controller
{
    public function __construct(private readonly HousekeepingService $service)
    {
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'task_type', 'priority']);
        $tasks = $this->service->paginate($filters);
        
        return Inertia::render('Housekeeping/Tasks/Index', [
            'tasks' => $tasks,
            'filters' => $filters,
        ]);
    }

    public function show(int $id): Response
    {
        $task = $this->service->findOrFail($id, ['room', 'hotel', 'createdBy']);
        
        return Inertia::render('Housekeeping/Tasks/Show', [
            'task' => $task,
        ]);
    }

    public function store(StoreHousekeepingTaskRequest $request): RedirectResponse
    {
        $task = $this->service->create($request->validated());
        
        return redirect()
            ->route('housekeeping.tasks.show', $task->id)
            ->with('success', 'Task created successfully.');
    }

    public function update(UpdateHousekeepingTaskRequest $request, int $id): RedirectResponse
    {
        $task = $this->service->update($id, $request->validated());
        
        return redirect()
            ->route('housekeeping.tasks.show', $task->id)
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);
        
        return redirect()
            ->route('housekeeping.tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,blocked',
        ]);
        
        $status = \App\Modules\Housekeeping\Enums\HousekeepingStatus::from($validated['status']);
        $task = $this->service->updateStatus($id, $status);
        
        return redirect()
            ->route('housekeeping.tasks.show', $task->id)
            ->with('success', 'Task status updated successfully.');
    }
}
```

#### API Controller (similar pattern)
#### Request Validators
#### API Resources

---

### 2. POS Module

#### Service Layer (`app/Modules/Pos/Services/PosService.php`)
```php
<?php

declare(strict_types=1);

namespace App\Modules\Pos\Services;

use App\Base\BaseService;
use App\Modules\Pos\Models\PosOrder;
use App\Modules\Pos\Enums\POSOrderStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

readonly class PosService extends BaseService
{
    public function __construct(
        private PosOrder $model
    ) {
        parent::setModel($model);
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->model->query()
            ->with(['hotel', 'createdBy'])
            ->applyFilters($filters)
            ->latest('scheduled_at')
            ->paginate(15);
    }

    public function create(array $payload): PosOrder
    {
        $payload['reference'] = $this->generateReference();
        $payload['status'] = $payload['status'] ?? POSOrderStatus::Draft->value;
        
        return $this->model->create($payload);
    }

    public function chargeToRoom(int $orderId, int $reservationId): PosOrder
    {
        $order = $this->findOrFail($orderId);
        $reservation = \App\Modules\FrontDesk\Models\Reservation::findOrFail($reservationId);
        
        $order->update([
            'reservation_id' => $reservationId,
            'guest_name' => $reservation->guestProfile->full_name,
            'room_number' => $reservation->room->number,
        ]);
        
        // Add to reservation folio
        $reservation->update([
            'total_amount' => $reservation->total_amount + $order->total_amount,
        ]);
        
        return $order->fresh();
    }

    public function getByOutlet(int $hotelId, string $outlet): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->where('outlet', $outlet)
            ->whereDate('scheduled_at', today())
            ->latest('scheduled_at')
            ->get();
    }

    public function getTodayOrders(int $hotelId): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->whereDate('scheduled_at', today())
            ->latest('scheduled_at')
            ->get();
    }

    private function generateReference(): string
    {
        return 'POS-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
```

#### Menu Item Service (`app/Modules/Pos/Services/MenuItemService.php`)
```php
<?php

declare(strict_types=1);

namespace App\Modules\Pos\Services;

use App\Base\BaseService;
use App\Modules\Pos\Models\PosMenuItem;
use Illuminate\Database\Eloquent\Collection;

readonly class MenuItemService extends BaseService
{
    public function __construct(
        private PosMenuItem $model
    ) {
        parent::setModel($model);
    }

    public function getMenu(int $hotelId, ?string $category = null): Collection
    {
        $query = $this->model->where('hotel_id', $hotelId)
            ->where('is_active', true);
        
        if ($category) {
            $query->where('category', $category);
        }
        
        return $query->orderBy('category')->orderBy('name')->get();
    }

    public function getCategories(int $hotelId): array
    {
        return $this->model->where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->distinct()
            ->pluck('category')
            ->toArray();
    }
}
```

---

## 📁 Files to Create

### Housekeeping Module (8 files)
1. `app/Modules/Housekeeping/Services/HousekeepingService.php`
2. `app/Modules/Housekeeping/Controllers/Web/HousekeepingTaskController.php`
3. `app/Modules/Housekeeping/Controllers/Api/V1/HousekeepingTaskController.php`
4. `app/Modules/Housekeeping/Requests/StoreHousekeepingTaskRequest.php`
5. `app/Modules/Housekeeping/Requests/UpdateHousekeepingTaskRequest.php`
6. `app/Modules/Housekeeping/Resources/HousekeepingTaskResource.php`
7. `tests/Feature/Api/HousekeepingApiTest.php`
8. `tests/Feature/Modules/Housekeeping/HousekeepingWebTest.php`

### POS Module (10 files)
1. `app/Modules/Pos/Services/PosService.php`
2. `app/Modules/Pos/Services/MenuItemService.php`
3. `app/Modules/Pos/Controllers/Web/PosOrderController.php`
4. `app/Modules/Pos/Controllers/Web/MenuItemController.php`
5. `app/Modules/Pos/Controllers/Api/V1/PosOrderController.php`
6. `app/Modules/Pos/Controllers/Api/V1/MenuItemController.php`
7. `app/Modules/Pos/Requests/StorePosOrderRequest.php`
8. `app/Modules/Pos/Requests/StoreMenuItemRequest.php`
9. `app/Modules/Pos/Resources/PosOrderResource.php`
10. `app/Modules/Pos/Resources/MenuItemResource.php`

---

## 🎯 Routes to Add

### Web Routes (`routes/web.php`)
```php
// Housekeeping Routes
Route::middleware(['web', 'auth'])->prefix('housekeeping')->name('housekeeping.')->group(function (): void {
    Route::get('/tasks', [HousekeepingTaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{id}', [HousekeepingTaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks', [HousekeepingTaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{id}', [HousekeepingTaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{id}', [HousekeepingTaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/{id}/status', [HousekeepingTaskController::class, 'updateStatus'])->name('tasks.update-status');
});

// POS Routes
Route::middleware(['web', 'auth'])->prefix('pos')->name('pos.')->group(function (): void {
    Route::get('/orders', [PosOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [PosOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders', [PosOrderController::class, 'store'])->name('orders.store');
    Route::put('/orders/{id}', [PosOrderController::class, 'update'])->name('orders.update');
    Route::post('/orders/{id}/charge-to-room', [PosOrderController::class, 'chargeToRoom'])->name('orders.charge-to-room');
    
    Route::get('/menu', [MenuItemController::class, 'index'])->name('menu.index');
    Route::post('/menu', [MenuItemController::class, 'store'])->name('menu.store');
    Route::put('/menu/{id}', [MenuItemController::class, 'update'])->name('menu.update');
    Route::delete('/menu/{id}', [MenuItemController::class, 'destroy'])->name('menu.destroy');
});
```

### API Routes (`routes/api.php`)
```php
// Housekeeping Routes
Route::apiResource('housekeeping/tasks', HousekeepingTaskController::class);
Route::post('housekeeping/tasks/{id}/status', [HousekeepingTaskController::class, 'updateStatus']);

// POS Routes
Route::apiResource('pos/orders', PosOrderController::class);
Route::post('pos/orders/{id}/charge-to-room', [PosOrderController::class, 'chargeToRoom']);
Route::apiResource('pos/menu', MenuItemController::class);
```

---

## ✅ Implementation Checklist

### Housekeeping Module
- [ ] Create HousekeepingService
- [ ] Create HousekeepingTaskController (Web)
- [ ] Create HousekeepingTaskController (API)
- [ ] Create StoreHousekeepingTaskRequest
- [ ] Create UpdateHousekeepingTaskRequest
- [ ] Create HousekeepingTaskResource
- [ ] Update routes (web + API)
- [ ] Write tests
- [ ] Create Vue components

### POS Module
- [ ] Create PosService
- [ ] Create MenuItemService
- [ ] Create PosOrderController (Web)
- [ ] Create MenuItemController (Web)
- [ ] Create Controllers (API)
- [ ] Create StorePosOrderRequest
- [ ] Create StoreMenuItemRequest
- [ ] Create PosOrderResource
- [ ] Create MenuItemResource
- [ ] Update routes (web + API)
- [ ] Write tests
- [ ] Create Vue components

---

## 🚀 Quick Start Commands

```bash
# Generate Housekeeping files
php artisan make:service HousekeepingService
php artisan make:controller HousekeepingTaskController
php artisan make:request StoreHousekeepingTaskRequest
php artisan make:request UpdateHousekeepingTaskRequest
php artisan make:resource HousekeepingTaskResource

# Generate POS files
php artisan make:service PosService
php artisan make:service MenuItemService
php artisan make:controller PosOrderController
php artisan make:controller MenuItemController
php artisan make:request StorePosOrderRequest
php artisan make:request StoreMenuItemRequest
php artisan make:resource PosOrderResource
php artisan make:resource MenuItemResource
```

---

## 📝 Next Steps

1. ✅ DatabaseSeeder updated with comprehensive data
2. ⏳ Implement Housekeeping Module (2-3 hours)
3. ⏳ Implement POS Module (3-4 hours)
4. ⏳ Write tests for both modules (2 hours)
5. ⏳ Create frontend components (4-6 hours)

---

*This guide provides the complete implementation pattern. Follow the same structure as Reservation, Guest, and Room modules.*

*Last Updated: March 17, 2026*
