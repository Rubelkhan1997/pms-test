<?php

declare(strict_types=1);

use App\Modules\Auth\Controllers\Api\V1\AuthController;
use App\Modules\FrontDesk\Controllers\Api\V1\HotelController;
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

        // Hotels API (Full CRUD)
        Route::prefix('front-desk')->group(function (): void {
            Route::get('hotels', [HotelController::class, 'index'])->name('front-desk.hotels.index')->middleware('permission:view hotels');
            Route::get('hotels/{id}', [HotelController::class, 'show'])->name('front-desk.hotels.show')->middleware('permission:view hotels');
            Route::post('hotels', [HotelController::class, 'store'])->name('front-desk.hotels.store')->middleware('permission:create hotels');
            Route::put('hotels/{id}', [HotelController::class, 'update'])->name('front-desk.hotels.update')->middleware('permission:edit hotels');
            Route::delete('hotels/{id}', [HotelController::class, 'destroy'])->name('front-desk.hotels.destroy')->middleware('permission:delete hotels');
        });

        // Reservations API (Full CRUD)
        Route::apiResource('front-desk/reservations', FrontDeskController::class)->only(['index', 'show'])->middleware('permission:view reservations');
        Route::post('front-desk/reservations', [FrontDeskController::class, 'store'])->middleware('permission:create reservations');
        Route::put('front-desk/reservations/{reservation}', [FrontDeskController::class, 'update'])->middleware('permission:edit reservations');
        Route::delete('front-desk/reservations/{reservation}', [FrontDeskController::class, 'destroy'])->middleware('permission:delete reservations'); 
        Route::patch('front-desk/reservations/{reservation}/cancel', [FrontDeskController::class, 'cancel'])->middleware('permission:edit reservations');
    });
});
