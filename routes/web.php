<?php

declare(strict_types=1);

use App\Modules\Auth\Controllers\Web\AuthController;
use App\Modules\Auth\Middleware\RedirectIfAuthenticated;
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
// Reservations Routes (FrontDesk) - WEB ONLY (Page Views)
// ─────────────────────────────────────────────────────────
Route::middleware(['auth.token'])->prefix('reservations')->name('reservations.')->group(function (): void {
    // List all reservations (page view)
    Route::get('/', [FrontDeskController::class, 'index'])
        ->middleware('permission:view reservations')
        ->name('index');

    // Create reservation form (page view)
    Route::get('/create', [FrontDeskController::class, 'create'])
        ->middleware('permission:create reservations')
        ->name('create');

    // View single reservation (page view)
    Route::get('/{reservation}', [FrontDeskController::class, 'show'])
        ->middleware('permission:view reservations')
        ->name('show');

    // Edit reservation form (page view)
    Route::get('/{reservation}/edit', [FrontDeskController::class, 'edit'])
        ->middleware('permission:edit reservations')
        ->name('edit');
});
 
