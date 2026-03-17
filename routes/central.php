<?php

declare(strict_types=1);

use App\Http\Controllers\Central\CentralAdminController;
use App\Http\Controllers\Central\CentralTenantController;
use App\Http\Controllers\Central\CentralAuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Central Website Routes (Public)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Tenant Registration
    Route::get('/register', [CentralTenantController::class, 'create'])
        ->name('central.register');
    Route::post('/register', [CentralTenantController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Central Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('central')->name('central.')->group(function () {
    // Public routes
    Route::get('/', function () {
        return Inertia::render('Central/Home');
    })->name('home');

    Route::middleware('guest')->group(function () {
        Route::get('login', [CentralAuthController::class, 'create'])
            ->name('login');
        Route::post('login', [CentralAuthController::class, 'store']);
    });

    // Protected central admin routes
    Route::middleware(['auth', 'role:super_admin'])->group(function () {
        // Dashboard
        Route::get('dashboard', [CentralAdminController::class, 'dashboard'])
            ->name('dashboard');

        // Tenant Management
        Route::get('tenants', [CentralTenantController::class, 'index'])
            ->name('tenants.index');
        Route::get('tenants/create', [CentralTenantController::class, 'create'])
            ->name('tenants.create');
        Route::post('tenants', [CentralTenantController::class, 'store'])
            ->name('tenants.store');
        Route::get('tenants/{tenant}', [CentralAdminController::class, 'showTenant'])
            ->name('tenants.show');
        Route::post('tenants/{tenant}/approve', [CentralTenantController::class, 'approve'])
            ->name('tenants.approve');
        Route::post('tenants/{tenant}/reject', [CentralTenantController::class, 'reject'])
            ->name('tenants.reject');
        Route::post('tenants/{tenant}/suspend', [CentralTenantController::class, 'suspend'])
            ->name('tenants.suspend');
        Route::post('tenants/{tenant}/reactivate', [CentralTenantController::class, 'reactivate'])
            ->name('tenants.reactivate');

        // Admin Profile
        Route::get('profile', [CentralAdminController::class, 'profile'])
            ->name('profile');
        Route::put('profile', [CentralAdminController::class, 'updateProfile']);

        // Logout
        Route::post('logout', [CentralAuthController::class, 'destroy'])
            ->name('logout');
    });
});
