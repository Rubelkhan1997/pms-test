<?php

declare(strict_types=1);
 
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
// Reservations Routes (FrontDesk) - WEB ONLY (Page Views)
// ─────────────────────────────────────────────────────────
// middleware(['web', 'auth'])->
Route::prefix('reservations')->name('reservations.')->group(function (): void {
    // List all reservations (page view)
    Route::get('/', [FrontDeskController::class, 'index'])->name('index');

    // Create reservation form (page view)
    Route::get('/create', [FrontDeskController::class, 'create'])->name('create');

    // View single reservation (page view)
    Route::get('/{reservation}', [FrontDeskController::class, 'show'])->name('show');

    // Edit reservation form (page view)
    Route::get('/{reservation}/edit', [FrontDeskController::class, 'edit'])->name('edit');
});
 