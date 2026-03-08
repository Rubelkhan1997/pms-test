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

Route::prefix('front-desk')->name('front-desk.')->group(function (): void {
    Route::get('/reservations', [FrontDeskController::class, 'index'])->name('reservations.index');
    Route::post('/reservations', [FrontDeskController::class, 'store'])->name('reservations.store');
});

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
