<?php

declare(strict_types=1);

use App\Modules\Auth\Controllers\Web\AuthController as LoginController;
use App\Modules\SuperAdmin\Controllers\Web\DashboardController;
use App\Modules\SuperAdmin\Controllers\Web\TenantController;
use App\Modules\SuperAdmin\Controllers\Web\SubscriptionPlanController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'showSuperAdminLogin'])->name('super-admin.login')->middleware('guest:super-admin');
// Route::post('/login', [LoginController::class, 'login'])->name('super-admin.login.submit');
// Route::post('/logout', [LoginController::class, 'logout'])->name('super-admin.logout');

Route::get('/', fn () => redirect()->route('super-admin.dashboard'))->name('super-admin.home');

Route::middleware(['auth:super-admin'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('super-admin.dashboard');

    // Tenants
    Route::prefix('tenants')->name('super-admin.tenants.')->group(function (): void {
        Route::get('/',           [TenantController::class, 'index'])->name('index');
        Route::get('/create',     [TenantController::class, 'create'])->name('create');
        Route::post('/',          [TenantController::class, 'store'])->name('store');
        Route::get('/{id}',       [TenantController::class, 'show'])->name('show');
        Route::get('/{id}/edit',  [TenantController::class, 'edit'])->name('edit');
        Route::put('/{id}',       [TenantController::class, 'update'])->name('update');
    });

    // Subscription Plans
    Route::prefix('plans')->name('super-admin.plans.')->group(function (): void {
        Route::get('/',          [SubscriptionPlanController::class, 'index'])->name('index');
        Route::get('/create',    [SubscriptionPlanController::class, 'create'])->name('create');
        Route::post('/',         [SubscriptionPlanController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [SubscriptionPlanController::class, 'edit'])->name('edit');
        Route::put('/{id}',      [SubscriptionPlanController::class, 'update'])->name('update');
        Route::delete('/{id}',   [SubscriptionPlanController::class, 'destroy'])->name('destroy');
    });
});
