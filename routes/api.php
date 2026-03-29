<?php

declare(strict_types=1);

use App\Modules\FrontDesk\Controllers\Api\V1\ReservationController as FrontDeskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function (): void {
    Route::get('/user', static fn (Request $request): mixed => $request->user());

    // Reservations API (Full CRUD)
    Route::apiResource('front-desk/reservations', FrontDeskController::class);
    Route::patch('front-desk/reservations/{reservation}/check-in', [FrontDeskController::class, 'checkIn']);
    Route::patch('front-desk/reservations/{reservation}/check-out', [FrontDeskController::class, 'checkOut']);
    Route::patch('front-desk/reservations/{reservation}/cancel', [FrontDeskController::class, 'cancel']);
});
