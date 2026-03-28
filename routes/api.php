<?php

declare(strict_types=1);

use App\Modules\Booking\Controllers\Api\V1\OtaSyncController as BookingController;
use App\Modules\FrontDesk\Controllers\Api\V1\ReservationController as FrontDeskController;
use App\Modules\Guest\Controllers\Api\V1\GuestProfileController as GuestController;
use App\Modules\Housekeeping\Controllers\Api\V1\HousekeepingTaskController as HousekeepingController;
use App\Modules\Hr\Controllers\Api\V1\EmployeeController as HrController;
use App\Modules\Mobile\Controllers\Api\V1\MobileTaskController as MobileController;
use App\Modules\Pos\Controllers\Api\V1\PosOrderController as PosController;
use App\Modules\Reports\Controllers\Api\V1\ReportSnapshotController as ReportsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// middleware(['auth:sanctum'])->
Route::prefix('v1')->group(function (): void {
    Route::get('/user', static fn (Request $request): mixed => $request->user());

    // Reservations API (Full CRUD)
    Route::apiResource('front-desk/reservations', FrontDeskController::class);
    Route::patch('front-desk/reservations/{reservation}/check-in', [FrontDeskController::class, 'checkIn']);
    Route::patch('front-desk/reservations/{reservation}/check-out', [FrontDeskController::class, 'checkOut']);
    Route::patch('front-desk/reservations/{reservation}/cancel', [FrontDeskController::class, 'cancel']);
});
