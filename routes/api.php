<?php

declare(strict_types=1);

use App\Modules\Auth\Controllers\Api\V1\AuthController;
use App\Modules\FrontDesk\Controllers\Api\V1\HotelController;
use App\Modules\FrontDesk\Controllers\Api\V1\RoomController as FrontDeskRoomController;
use App\Modules\FrontDesk\Controllers\Api\V1\ReservationController as FrontDeskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Auth routes (public - no auth required)
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:10,1');

    // Protected routes (require Sanctum token)
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::get('/user', static fn (Request $request): mixed => $request->user());

        Route::prefix('front-desk')->group(function (): void {
            Route::apiResource('hotels', HotelController::class)->only(['index', 'show'])->middleware('permission:view hotels');
            Route::post('hotels', [HotelController::class, 'store'])->name('front-desk.hotels.store')->middleware('permission:create hotels');
            Route::put('hotels/{id}', [HotelController::class, 'update'])->name('front-desk.hotels.update')->middleware('permission:edit hotels');
            Route::delete('hotels/{id}', [HotelController::class, 'destroy'])->name('front-desk.hotels.destroy')->middleware('permission:delete hotels');

            // Rooms API (Full CRUD)
            Route::apiResource('rooms', FrontDeskRoomController::class)->only(['index', 'show'])->middleware('permission:view rooms');
            Route::post('rooms', [FrontDeskRoomController::class, 'store'])->middleware('permission:create rooms');
            Route::put('rooms/{id}', [FrontDeskRoomController::class, 'update'])->middleware('permission:edit rooms');
            Route::delete('rooms/{id}', [FrontDeskRoomController::class, 'destroy'])->middleware('permission:delete rooms');
            
            // Reservations API (Full CRUD)
            Route::apiResource('reservations', FrontDeskController::class)->only(['index', 'show'])->middleware('permission:view reservations');
            Route::post('reservations', [FrontDeskController::class, 'store'])->middleware('permission:create reservations');
            Route::put('reservations/{id}', [FrontDeskController::class, 'update'])->middleware('permission:edit reservations');
            Route::delete('reservations/{id}', [FrontDeskController::class, 'destroy'])->middleware('permission:delete reservations'); 
            Route::patch('reservations/{id}/cancel', [FrontDeskController::class, 'cancel'])->middleware('permission:edit reservations');
        });

    });
});
