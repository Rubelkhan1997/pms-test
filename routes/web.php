<?php

declare(strict_types=1);

use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Auth\Middleware\Authenticate;
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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard/Index');
})->name('dashboard');

// ─────────────────────────────────────────────────────────
// Authentication Routes
// ─────────────────────────────────────────────────────────
Route::middleware(RedirectIfAuthenticated::class)->group(function (): void {
    // Login routes
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Register routes
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout route (requires auth)
Route::middleware(Authenticate::class)->post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─────────────────────────────────────────────────────────
// Reservations Routes (FrontDesk) - WEB ONLY (Page Views)
// ─────────────────────────────────────────────────────────
Route::middleware(Authenticate::class)->prefix('reservations')->name('reservations.')->group(function (): void {
    // List all reservations (page view)
    Route::get('/', [FrontDeskController::class, 'index'])->name('index');

    // Create reservation form (page view)
    Route::get('/create', [FrontDeskController::class, 'create'])->name('create');

    // View single reservation (page view)
    Route::get('/{reservation}', [FrontDeskController::class, 'show'])->name('show');

    // Edit reservation form (page view)
    Route::get('/{reservation}/edit', [FrontDeskController::class, 'edit'])->name('edit');
});
 