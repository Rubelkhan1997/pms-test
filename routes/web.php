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
])->group(base_path('routes/tenant.php'));

// ─────────────────────────────────────────────────────────
// Hotels Routes (FrontDesk) - WEB ONLY (Page Views)
// ─────────────────────────────────────────────────────────
Route::middleware(['auth.token'])->prefix('hotels')->name('hotels.')->group(function (): void {
    Route::get('/', [HotelController::class, 'index'])->name('index')->middleware('permission:view hotels');
    Route::get('/create', [HotelController::class, 'create'])->name('create')->middleware('permission:create hotels');
    Route::get('/{hotel}', [HotelController::class, 'show'])->name('show')->middleware('permission:view hotels');
    Route::get('/{hotel}/edit', [HotelController::class, 'edit'])->name('edit')->middleware('permission:edit hotels');
});

// ─────────────────────────────────────────────────────────
// Rooms Routes (FrontDesk) - WEB ONLY (Page Views)
// ─────────────────────────────────────────────────────────
Route::middleware(['auth.token'])->prefix('rooms')->name('rooms.')->group(function (): void {
    Route::get('/', [RoomController::class, 'index'])->name('index')->middleware('permission:view rooms');
    Route::get('/create', [RoomController::class, 'create'])->name('create')->middleware('permission:create rooms');
    Route::get('/{room}', [RoomController::class, 'show'])->name('show')->middleware('permission:view rooms');
    Route::get('/{room}/edit', [RoomController::class, 'edit'])->name('edit')->middleware('permission:edit rooms');
});

// ─────────────────────────────────────────────────────────
// Reservations Routes (FrontDesk) - WEB ONLY (Page Views)
// ─────────────────────────────────────────────────────────
Route::middleware(['auth.token'])->prefix('reservations')->name('reservations.')->group(function (): void {
    Route::get('/', [FrontDeskController::class, 'index'])->name('index')->middleware('permission:view reservations');
    Route::get('/create', [FrontDeskController::class, 'create'])->name('create')->middleware('permission:create reservations');
    Route::get('/{reservation}', [FrontDeskController::class, 'show'])->name('show')->middleware('permission:view reservations');
    Route::get('/{reservation}/edit', [FrontDeskController::class, 'edit'])->name('edit')->middleware('permission:edit reservations');
});

