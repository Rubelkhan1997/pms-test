<?php

declare(strict_types=1);

use App\Modules\SuperAdmin\Controllers\Web\DashboardController;
use App\Modules\SuperAdmin\Controllers\Web\TenantController;
use App\Modules\SuperAdmin\Controllers\Web\SubscriptionPlanController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('super-admin.dashboard'))->name('super-admin.home');

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('super-admin.dashboard');

    // Tenants
    Route::prefix('tenants')->name('super-admin.tenants.')->group(function (): void {
        Route::get('/',           [TenantController::class, 'index'])->name('index');
        Route::get('/create',     [TenantController::class, 'create'])->name('create');
        Route::get('/{id}',       [TenantController::class, 'show'])->name('show');
        Route::get('/{id}/edit',  [TenantController::class, 'edit'])->name('edit');
    });

    // Subscription Plans
    Route::prefix('plans')->name('super-admin.plans.')->group(function (): void {
        Route::get('/',          [SubscriptionPlanController::class, 'index'])->name('index');
        Route::get('/create',    [SubscriptionPlanController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [SubscriptionPlanController::class, 'edit'])->name('edit');
    });
});
