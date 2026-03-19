<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Modules\Booking\Controllers\Web\OtaSyncController as BookingController;
use App\Modules\FrontDesk\Controllers\Web\ReservationController as FrontDeskController;
use App\Modules\FrontDesk\Controllers\Web\RoomController;
use App\Modules\Guest\Controllers\Web\GuestProfileController as GuestController;
use App\Modules\Housekeeping\Controllers\Web\HousekeepingTaskController as HousekeepingController;
use App\Modules\Hr\Controllers\Web\EmployeeController as HrController;
use App\Modules\Mobile\Controllers\Web\MobileTaskController as MobileController;
use App\Modules\Pos\Controllers\Web\MenuItemController;
use App\Modules\Pos\Controllers\Web\PosOrderController;
use App\Modules\Pos\Controllers\Web\PosOrderController as PosController;
use App\Modules\Reports\Controllers\Web\ReportSnapshotController as ReportsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Include Central Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/central.php';

/*
|--------------------------------------------------------------------------
| Root Redirect
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Tenant Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['guest', 'tenant.subdomain'])->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
    
    Route::post('tenant/switch/{tenant}', [AuthenticatedSessionController::class, 'switchTenant'])
        ->name('tenant.switch');
});

/*
|--------------------------------------------------------------------------
| Dashboard Route
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'tenant'])->get('/dashboard', function () {
    return inertia('Dashboard/Index');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Front Desk Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth', 'tenant'])->prefix('front-desk')->name('front-desk.')->group(function (): void {
    // Reservations
    Route::get('/reservations', [FrontDeskController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{id}', [FrontDeskController::class, 'show'])->name('reservations.show');
    Route::post('/reservations', [FrontDeskController::class, 'store'])->name('reservations.store');
    Route::put('/reservations/{id}', [FrontDeskController::class, 'update'])->name('reservations.update');
    Route::delete('/reservations/{id}', [FrontDeskController::class, 'destroy'])->name('reservations.destroy');
    
    // Reservation workflows
    Route::post('/reservations/{id}/check-in', [FrontDeskController::class, 'checkIn'])->name('reservations.check-in');
    Route::post('/reservations/{id}/check-out', [FrontDeskController::class, 'checkOut'])->name('reservations.check-out');
    Route::post('/reservations/{id}/cancel', [FrontDeskController::class, 'cancel'])->name('reservations.cancel');
    
    // Rooms
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::get('/rooms/grid', [RoomController::class, 'grid'])->name('rooms.grid');
    Route::get('/rooms/{id}', [RoomController::class, 'show'])->name('rooms.show');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::put('/rooms/{id}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{id}', [RoomController::class, 'destroy'])->name('rooms.destroy');
    Route::post('/rooms/{id}/status', [RoomController::class, 'updateStatus'])->name('rooms.update-status');
});

/*
|--------------------------------------------------------------------------
| Booking Routes (OTA)
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth', 'tenant'])->prefix('booking')->name('booking.')->group(function (): void {
    Route::get('/ota-syncs', [BookingController::class, 'index'])->name('ota-syncs.index');
    Route::post('/ota-syncs', [BookingController::class, 'store'])->name('ota-syncs.store');
});

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth', 'tenant'])->prefix('guests')->name('guests.')->group(function (): void {
    Route::get('/profiles', [GuestController::class, 'index'])->name('profiles.index');
    Route::get('/profiles/search', [GuestController::class, 'search'])->name('profiles.search');
    Route::get('/profiles/vip', [GuestController::class, 'vip'])->name('profiles.vip');
    Route::get('/profiles/{id}', [GuestController::class, 'show'])->name('profiles.show');
    Route::post('/profiles', [GuestController::class, 'store'])->name('profiles.store');
    Route::put('/profiles/{id}', [GuestController::class, 'update'])->name('profiles.update');
    Route::delete('/profiles/{id}', [GuestController::class, 'destroy'])->name('profiles.destroy');
});

/*
|--------------------------------------------------------------------------
| Housekeeping Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth', 'tenant'])->prefix('housekeeping')->name('housekeeping.')->group(function (): void {
    Route::get('/tasks', [HousekeepingController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/today', [HousekeepingController::class, 'today'])->name('tasks.today');
    Route::get('/tasks/{id}', [HousekeepingController::class, 'show'])->name('tasks.show');
    Route::post('/tasks', [HousekeepingController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{id}', [HousekeepingController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{id}', [HousekeepingController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/{id}/status', [HousekeepingController::class, 'updateStatus'])->name('tasks.update-status');
});

/*
|--------------------------------------------------------------------------
| POS Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth', 'tenant'])->prefix('pos')->name('pos.')->group(function (): void {
    Route::get('/orders', [PosOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/today', [PosOrderController::class, 'today'])->name('orders.today');
    Route::get('/orders/{id}', [PosOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders', [PosOrderController::class, 'store'])->name('orders.store');
    Route::put('/orders/{id}', [PosOrderController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{id}', [PosOrderController::class, 'destroy'])->name('orders.destroy');
    Route::post('/orders/{id}/status', [PosOrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{id}/charge-to-room', [PosOrderController::class, 'chargeToRoom'])->name('orders.charge-to-room');
    
    // Menu
    Route::get('/menu', [MenuItemController::class, 'index'])->name('menu.index');
    Route::post('/menu', [MenuItemController::class, 'store'])->name('menu.store');
    Route::put('/menu/{id}', [MenuItemController::class, 'update'])->name('menu.update');
    Route::delete('/menu/{id}', [MenuItemController::class, 'destroy'])->name('menu.destroy');
    Route::post('/menu/{id}/toggle', [MenuItemController::class, 'toggleActive'])->name('menu.toggle-active');
});

/*
|--------------------------------------------------------------------------
| Reports Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth', 'tenant'])->prefix('reports')->name('reports.')->group(function (): void {
    Route::get('/snapshots', [ReportsController::class, 'index'])->name('snapshots.index');
    Route::post('/snapshots', [ReportsController::class, 'store'])->name('snapshots.store');
});

/*
|--------------------------------------------------------------------------
| Mobile Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth', 'tenant'])->prefix('mobile')->name('mobile.')->group(function (): void {
    Route::get('/tasks', [MobileController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [MobileController::class, 'store'])->name('tasks.store');
});

/*
|--------------------------------------------------------------------------
| HR Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth', 'tenant'])->prefix('hr')->name('hr.')->group(function (): void {
    Route::get('/employees', [HrController::class, 'index'])->name('employees.index');
    Route::get('/employees/{id}', [HrController::class, 'show'])->name('employees.show');
    Route::post('/employees', [HrController::class, 'store'])->name('employees.store');
    Route::put('/employees/{id}', [HrController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{id}', [HrController::class, 'destroy'])->name('employees.destroy');
});
