<?php

declare(strict_types=1);

use App\Http\Controllers\PropertyEntryController;
use App\Modules\FrontDesk\Controllers\Web\HotelController;
use App\Modules\FrontDesk\Controllers\Web\ReservationController as FrontDeskController;
use App\Modules\FrontDesk\Controllers\Web\RoomController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['guest'])->group(function (): void {
    Route::get('/login', [App\Modules\Auth\Controllers\Web\AuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [App\Modules\Auth\Controllers\Web\AuthController::class, 'showRegister'])->name('register');
});

// Onboarding wizard (must come BEFORE the auth.token + ensure.property.onboarded group)
Route::middleware(['auth.token', 'ensure.subscription.active'])->prefix('onboarding')->name('onboarding.')->group(function (): void {
    // Route::get('/property/create', fn () => Inertia::render('Partner/Onboarding/Property/Create'))->name('property.create');
    
});

Route::get('/property/create', [PropertyEntryController::class, 'create'])->name('property.create');
Route::get('/property/rate-plan/create', [PropertyEntryController::class, 'ratePlanCreate'])->name('property.ratePlanCreate');
Route::get('/property/policies/create', [PropertyEntryController::class, 'policiesCreate'])->name('property.policiesCreate');
Route::get('/property/tex/create', [PropertyEntryController::class, 'TaxCreate'])->name('property.TaxCreate');
Route::get('/property/payment-method', [PropertyEntryController::class, 'PaymentMethodCreate'])->name('property.PaymentMethodCreate');
Route::get('/property/market', [PropertyEntryController::class, 'marketCreate'])->name('property.marketCreate');
Route::get('/property/rooms', [PropertyEntryController::class, 'roomList'])->name('property.roomList');
Route::get('/property/rate-type', [PropertyEntryController::class, 'RoomType'])->name('property.RoomType');



// All protected PMS routes
Route::middleware(['auth.token', 'ensure.subscription.active', 'ensure.property.onboarded'])->group(function (): void {
    Route::get('/dashboard', fn () => Inertia::render('Dashboard/Index'))->name('dashboard');

    Route::prefix('hotels')->name('hotels.')->group(function (): void {
        Route::get('/', [HotelController::class, 'index'])->name('index')->middleware('permission:view hotels');
        Route::get('/create', [HotelController::class, 'create'])->name('create')->middleware('permission:create hotels');
        Route::get('/{hotel}', [HotelController::class, 'show'])->name('show')->middleware('permission:view hotels');
        Route::get('/{hotel}/edit', [HotelController::class, 'edit'])->name('edit')->middleware('permission:edit hotels');
    });

    Route::prefix('rooms')->name('rooms.')->group(function (): void {
        Route::get('/', [RoomController::class, 'index'])->name('index')->middleware('permission:view rooms');
        Route::get('/create', [RoomController::class, 'create'])->name('create')->middleware('permission:create rooms');
        Route::get('/{room}', [RoomController::class, 'show'])->name('show')->middleware('permission:view rooms');
        Route::get('/{room}/edit', [RoomController::class, 'edit'])->name('edit')->middleware('permission:edit rooms');
    });

    Route::prefix('reservations')->name('reservations.')->group(function (): void {
        Route::get('/', [FrontDeskController::class, 'index'])->name('index')->middleware('permission:view reservations');
        Route::get('/create', [FrontDeskController::class, 'create'])->name('create')->middleware('permission:create reservations');
        Route::get('/{reservation}', [FrontDeskController::class, 'show'])->name('show')->middleware('permission:view reservations');
        Route::get('/{reservation}/edit', [FrontDeskController::class, 'edit'])->name('edit')->middleware('permission:edit reservations');
    });
});
