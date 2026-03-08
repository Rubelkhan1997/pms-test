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

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function (): void {
    Route::get('/user', static fn (Request $request): mixed => $request->user());

    Route::apiResource('front-desk/reservations', FrontDeskController::class)->only(['index', 'store']);
    Route::apiResource('booking/ota-syncs', BookingController::class)->only(['index', 'store']);
    Route::apiResource('guests/profiles', GuestController::class)->only(['index', 'store']);
    Route::apiResource('housekeeping/tasks', HousekeepingController::class)->only(['index', 'store']);
    Route::apiResource('pos/orders', PosController::class)->only(['index', 'store']);
    Route::apiResource('reports/snapshots', ReportsController::class)->only(['index', 'store']);
    Route::apiResource('mobile/tasks', MobileController::class)->only(['index', 'store']);
    Route::apiResource('hr/employees', HrController::class)->only(['index', 'store']);
});
