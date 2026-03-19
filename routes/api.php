<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\ApiAuthController;
use App\Modules\Booking\Controllers\Api\V1\OtaSyncController as BookingController;
use App\Modules\FrontDesk\Controllers\Api\V1\ReservationController as FrontDeskController;
use App\Modules\FrontDesk\Controllers\Api\V1\RoomController;
use App\Modules\Guest\Controllers\Api\V1\GuestProfileController as GuestController;
use App\Modules\Housekeeping\Controllers\Api\V1\HousekeepingTaskController as HousekeepingController;
use App\Modules\Hr\Controllers\Api\V1\EmployeeController;
use App\Modules\Hr\Controllers\Api\V1\EmployeeController as HrController;
use App\Modules\Mobile\Controllers\Api\V1\MobileTaskController as MobileController;
use App\Modules\Pos\Controllers\Api\V1\MenuItemController;
use App\Modules\Pos\Controllers\Api\V1\PosOrderController;
use App\Modules\Pos\Controllers\Api\V1\PosOrderController as PosController;
use App\Modules\Reports\Controllers\Api\V1\ReportSnapshotController as ReportsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function (): void {
    // Authentication
    Route::post('auth/login', [ApiAuthController::class, 'login']);
    Route::post('auth/register', [ApiAuthController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| Protected API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function (): void {
    // Auth
    Route::get('auth/user', [ApiAuthController::class, 'user']);
    Route::post('auth/logout', [ApiAuthController::class, 'logout']);
    Route::post('auth/tenant/{tenant}', [ApiAuthController::class, 'switchTenant']);
    
    // Current user endpoint
    Route::get('/user', static fn (Request $request): mixed => $request->user());

    Route::middleware(['tenant'])->group(function (): void {
        /*
        |--------------------------------------------------------------------------
        | Front Desk Routes (Reservations)
        |--------------------------------------------------------------------------
        */
        Route::apiResource('front-desk/reservations', FrontDeskController::class);
        Route::post('front-desk/reservations/{id}/check-in', [FrontDeskController::class, 'checkIn'])->name('api.v1.front-desk.reservations.check-in');
        Route::post('front-desk/reservations/{id}/check-out', [FrontDeskController::class, 'checkOut'])->name('api.v1.front-desk.reservations.check-out');
        Route::post('front-desk/reservations/{id}/cancel', [FrontDeskController::class, 'cancel'])->name('api.v1.front-desk.reservations.cancel');
        
        // Reservation reports
        Route::get('front-desk/reservations/reports/arrivals', [FrontDeskController::class, 'arrivals'])->name('api.v1.front-desk.reservations.arrivals');
        Route::get('front-desk/reservations/reports/departures', [FrontDeskController::class, 'departures'])->name('api.v1.front-desk.reservations.departures');
        Route::get('front-desk/reservations/reports/in-house', [FrontDeskController::class, 'inHouse'])->name('api.v1.front-desk.reservations.in-house');

        /*
        |--------------------------------------------------------------------------
        | Room Management Routes
        |--------------------------------------------------------------------------
        */
        Route::apiResource('front-desk/rooms', RoomController::class);
        Route::post('front-desk/rooms/{id}/status', [RoomController::class, 'updateStatus'])->name('api.v1.front-desk.rooms.update-status');
        Route::get('front-desk/rooms/hotel/{hotelId}', [RoomController::class, 'byHotel'])->name('api.v1.front-desk.rooms.by-hotel');
        Route::get('front-desk/rooms/hotel/{hotelId}/available', [RoomController::class, 'available'])->name('api.v1.front-desk.rooms.available');
        Route::get('front-desk/rooms/hotel/{hotelId}/statistics', [RoomController::class, 'statistics'])->name('api.v1.front-desk.rooms.statistics');
        Route::get('front-desk/rooms/hotel/{hotelId}/floors', [RoomController::class, 'floors'])->name('api.v1.front-desk.rooms.floors');
        Route::get('front-desk/rooms/hotel/{hotelId}/types', [RoomController::class, 'types'])->name('api.v1.front-desk.rooms.types');

        /*
        |--------------------------------------------------------------------------
        | Booking Routes (OTA)
        |--------------------------------------------------------------------------
        */
        Route::apiResource('booking/ota-syncs', BookingController::class)->only(['index', 'store']);

        /*
        |--------------------------------------------------------------------------
        | Guest Routes
        |--------------------------------------------------------------------------
        */
        Route::apiResource('guests/profiles', GuestController::class);
        Route::get('guests/profiles/search', [GuestController::class, 'search'])->name('api.v1.guests.profiles.search');
        Route::get('guests/profiles/vip', [GuestController::class, 'vip'])->name('api.v1.guests.profiles.vip');
        Route::get('guests/profiles/{id}/stay-history', [GuestController::class, 'stayHistory'])->name('api.v1.guests.profiles.stay-history');
        Route::post('guests/profiles/check-email', [GuestController::class, 'checkEmail'])->name('api.v1.guests.profiles.check-email');

        /*
        |--------------------------------------------------------------------------
        | Housekeeping Routes
        |--------------------------------------------------------------------------
        */
        Route::apiResource('housekeeping/tasks', HousekeepingController::class);
        Route::post('housekeeping/tasks/{id}/status', [HousekeepingController::class, 'updateStatus'])->name('api.v1.housekeeping.tasks.update-status');
        Route::get('housekeeping/tasks/hotel/{hotelId}/today', [HousekeepingController::class, 'today'])->name('api.v1.housekeeping.tasks.today');
        Route::get('housekeeping/tasks/hotel/{hotelId}/pending', [HousekeepingController::class, 'pending'])->name('api.v1.housekeeping.tasks.pending');
        Route::get('housekeeping/tasks/hotel/{hotelId}/statistics', [HousekeepingController::class, 'statistics'])->name('api.v1.housekeeping.tasks.statistics');

        /*
        |--------------------------------------------------------------------------
        | POS Routes
        |--------------------------------------------------------------------------
        */
        Route::apiResource('pos/orders', PosOrderController::class);
        Route::post('pos/orders/{id}/status', [PosOrderController::class, 'updateStatus'])->name('api.v1.pos.orders.update-status');
        Route::post('pos/orders/{id}/charge-to-room', [PosOrderController::class, 'chargeToRoom'])->name('api.v1.pos.orders.charge-to-room');
        Route::get('pos/orders/hotel/{hotelId}/today', [PosOrderController::class, 'today'])->name('api.v1.pos.orders.today');
        Route::get('pos/orders/hotel/{hotelId}/statistics', [PosOrderController::class, 'statistics'])->name('api.v1.pos.orders.statistics');
        
        // Menu Items
        Route::apiResource('pos/menu', MenuItemController::class);
        Route::post('pos/menu/{id}/toggle', [MenuItemController::class, 'toggleActive'])->name('api.v1.pos.menu.toggle-active');
        Route::get('pos/menu/hotel/{hotelId}', [MenuItemController::class, 'menu'])->name('api.v1.pos.menu.list');
        Route::get('pos/menu/hotel/{hotelId}/categories', [MenuItemController::class, 'categories'])->name('api.v1.pos.menu.categories');
        Route::get('pos/menu/hotel/{hotelId}/search', [MenuItemController::class, 'search'])->name('api.v1.pos.menu.search');

        /*
        |--------------------------------------------------------------------------
        | Reports Routes
        |--------------------------------------------------------------------------
        */
        Route::apiResource('reports/snapshots', ReportsController::class)->only(['index', 'store']);

        /*
        |--------------------------------------------------------------------------
        | Mobile Routes
        |--------------------------------------------------------------------------
        */
        Route::apiResource('mobile/tasks', MobileController::class)->only(['index', 'store']);

        /*
        |--------------------------------------------------------------------------
        | HR Routes
        |--------------------------------------------------------------------------
        */
        Route::apiResource('hr/employees', EmployeeController::class);
        Route::get('hr/employees/hotel/{hotelId}/active', [EmployeeController::class, 'active'])->name('api.v1.hr.employees.active');
        Route::get('hr/employees/hotel/{hotelId}/by-department', [EmployeeController::class, 'byDepartment'])->name('api.v1.hr.employees.by-department');
        Route::get('hr/employees/hotel/{hotelId}/statistics', [EmployeeController::class, 'statistics'])->name('api.v1.hr.employees.statistics');
        Route::get('hr/employees/hotel/{hotelId}/departments', [EmployeeController::class, 'departments'])->name('api.v1.hr.employees.departments');
    });
});
