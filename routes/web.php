<?php

declare(strict_types=1);

use App\Modules\Booking\Controllers\Web\OtaSyncController as BookingController;
use App\Modules\FrontDesk\Controllers\Web\ReservationController as FrontDeskController;
use App\Modules\Guest\Controllers\Web\GuestProfileController as GuestController;
use App\Modules\Housekeeping\Controllers\Web\HousekeepingTaskController as HousekeepingController;
use App\Modules\Hr\Controllers\Web\EmployeeController as HrController;
use App\Modules\Mobile\Controllers\Web\MobileTaskController as MobileController;
use App\Modules\Pos\Controllers\Web\PosOrderController as PosController;
use App\Modules\Reports\Controllers\Web\ReportSnapshotController as ReportsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ─────────────────────────────────────────────────────────
// Dashboard & Home Routes
// ─────────────────────────────────────────────────────────
Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard/Index');
})->name('dashboard');

// ─────────────────────────────────────────────────────────
// Reservations Routes (FrontDesk)
// ─────────────────────────────────────────────────────────
Route::prefix('reservations')->name('reservations.')->group(function (): void {
    // List all reservations
    Route::get('/', [FrontDeskController::class, 'index'])->name('index');

    // Create reservation (form) - MUST be before {reservation} route!
    Route::get('/create', [FrontDeskController::class, 'create'])->name('create');

    // View single reservation
    Route::get('/{reservation}', [FrontDeskController::class, 'show'])->name('show');

    // Store reservation
    Route::post('/', [FrontDeskController::class, 'store'])->name('store');

    // Edit reservation (form)
    Route::get('/{reservation}/edit', [FrontDeskController::class, 'edit'])->name('edit');

    // Update reservation
    Route::put('/{reservation}', [FrontDeskController::class, 'update'])->name('update');

    // Delete reservation
    Route::delete('/{reservation}', [FrontDeskController::class, 'destroy'])->name('destroy');

    // Actions
    Route::post('/{reservation}/check-in', [FrontDeskController::class, 'checkIn'])->name('check-in');
    Route::post('/{reservation}/check-out', [FrontDeskController::class, 'checkOut'])->name('check-out');
    Route::post('/{reservation}/cancel', [FrontDeskController::class, 'cancel'])->name('cancel');
});

// ─────────────────────────────────────────────────────────
// API Routes for Frontend (Composables/Services)
// ─────────────────────────────────────────────────────────
Route::prefix('api/v1')->name('api.v1.')->group(function (): void {
    // Reservations API
    Route::get('/front-desk/reservations', [FrontDeskController::class, 'index']);
    Route::get('/reservations/{reservation}', [FrontDeskController::class, 'show']);
    Route::post('/reservations', [FrontDeskController::class, 'store']);
    Route::put('/reservations/{reservation}', [FrontDeskController::class, 'update']);
    Route::delete('/reservations/{reservation}', [FrontDeskController::class, 'destroy']);
    Route::post('/reservations/{reservation}/check-in', [FrontDeskController::class, 'checkIn']);
    Route::post('/reservations/{reservation}/check-out', [FrontDeskController::class, 'checkOut']);
    Route::post('/reservations/{reservation}/cancel', [FrontDeskController::class, 'cancel']);
});

// ─────────────────────────────────────────────────────────
// Other Module Routes (Keep existing)
// ─────────────────────────────────────────────────────────
Route::middleware(['web', 'auth'])->prefix('booking')->name('booking.')->group(function (): void {
    Route::get('/ota-syncs', [BookingController::class, 'index'])->name('ota-syncs.index');
    Route::post('/ota-syncs', [BookingController::class, 'store'])->name('ota-syncs.store');
});

Route::middleware(['web', 'auth'])->prefix('guests')->name('guests.')->group(function (): void {
    Route::get('/profiles', [GuestController::class, 'index'])->name('profiles.index');
    Route::post('/profiles', [GuestController::class, 'store'])->name('profiles.store');
});

Route::middleware(['web', 'auth'])->prefix('housekeeping')->name('housekeeping.')->group(function (): void {
    Route::get('/tasks', [HousekeepingController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [HousekeepingController::class, 'store'])->name('tasks.store');
});

Route::middleware(['web', 'auth'])->prefix('pos')->name('pos.')->group(function (): void {
    Route::get('/orders', [PosController::class, 'index'])->name('orders.index');
    Route::post('/orders', [PosController::class, 'store'])->name('orders.store');
});

Route::middleware(['web', 'auth'])->prefix('reports')->name('reports.')->group(function (): void {
    Route::get('/snapshots', [ReportsController::class, 'index'])->name('snapshots.index');
    Route::post('/snapshots', [ReportsController::class, 'store'])->name('snapshots.store');
});

Route::middleware(['web', 'auth'])->prefix('mobile')->name('mobile.')->group(function (): void {
    Route::get('/tasks', [MobileController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [MobileController::class, 'store'])->name('tasks.store');
});

Route::middleware(['web', 'auth'])->prefix('hr')->name('hr.')->group(function (): void {
    Route::get('/employees', [HrController::class, 'index'])->name('employees.index');
    Route::post('/employees', [HrController::class, 'store'])->name('employees.store');
});
