<?php

declare(strict_types=1);

use App\Modules\Auth\Controllers\Web\AuthController;
use App\Modules\FrontDesk\Controllers\Web\HotelController;
use App\Modules\FrontDesk\Controllers\Web\ReservationController as FrontDeskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ─────────────────────────────────────────────────────────
// Dashboard & Home Routes
// ─────────────────────────────────────────────────────────
Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth.token'])->get('/dashboard', function () {
    return Inertia::render('Dashboard/Index');
})->name('dashboard');

// ─────────────────────────────────────────────────────────
// Authentication Routes (Inertia Pages)
// ─────────────────────────────────────────────────────────
Route::middleware(['guest'])->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
});

// ─────────────────────────────────────────────────────────
// Hotels Routes (FrontDesk) - WEB ONLY (Page Views)
// ─────────────────────────────────────────────────────────
Route::middleware(['auth.token'])->prefix('hotels')->name('hotels.')->group(function (): void {
    Route::get('/', [HotelController::class, 'index'])->name('index')->middleware('permission:view hotels');
    Route::get('/create', [HotelController::class, 'create'])->name('create')->middleware('permission:create hotels');
    Route::get('/{hotel}', [HotelController::class, 'show'])->name('show')->middleware('permission:view hotels');
    Route::get('/{hotel}/edit', [HotelController::class, 'edit'])->name('edit')->middleware('permission:edit hotels');
});

// ─────────────────────────────────────────────────────────
// Reservations Routes (FrontDesk) - WEB ONLY (Page Views)
// ─────────────────────────────────────────────────────────
Route::middleware(['auth.token'])->prefix('reservations')->name('reservations.')->group(function (): void {
    Route::get('/', [FrontDeskController::class, 'index'])->name('index')->middleware('permission:view reservations');
    Route::get('/create', [FrontDeskController::class, 'create'])->name('create')->middleware('permission:create reservations');
    Route::get('/{reservation}', [FrontDeskController::class, 'show'])->name('show')->middleware('permission:view reservations');
    Route::get('/{reservation}/edit', [FrontDeskController::class, 'edit'])->name('edit')->middleware('permission:edit reservations');
});
 
