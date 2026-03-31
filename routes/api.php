<?php

declare(strict_types=1);

use App\Modules\Auth\Controllers\Api\V1\AuthController;
use App\Modules\FrontDesk\Controllers\Api\V1\ReservationController as FrontDeskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Auth routes (public - no auth required)
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);

    // Protected routes (require Sanctum token)
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::get('/user', static fn (Request $request): mixed => $request->user());

        // Reservations API (Full CRUD)
        Route::apiResource('front-desk/reservations', FrontDeskController::class);
        Route::patch('front-desk/reservations/{reservation}/check-in', [FrontDeskController::class, 'checkIn']);
        Route::patch('front-desk/reservations/{reservation}/check-out', [FrontDeskController::class, 'checkOut']);
        Route::patch('front-desk/reservations/{reservation}/cancel', [FrontDeskController::class, 'cancel']);
    });
});
