<?php

declare(strict_types=1);

use App\Modules\SuperAdmin\Controllers\Api\V1\TenantController;
use App\Modules\SuperAdmin\Controllers\Api\V1\SubscriptionPlanController;
use App\Modules\Auth\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1/admin/auth')->group(function (): void {
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum'])->prefix('v1/admin')->group(function (): void {
    // Tenants
    Route::get('tenants',                 [TenantController::class, 'index']);
    Route::post('tenants',                [TenantController::class, 'store']);
    Route::get('tenants/{id}',            [TenantController::class, 'show']);
    Route::put('tenants/{id}',            [TenantController::class, 'update']);
    Route::patch('tenants/{id}/suspend',  [TenantController::class, 'suspend']);
    Route::patch('tenants/{id}/activate', [TenantController::class, 'activate']);

    // Subscription Plans
    Route::get('plans',        [SubscriptionPlanController::class, 'index']);
    Route::post('plans',       [SubscriptionPlanController::class, 'store']);
    Route::get('plans/{id}',   [SubscriptionPlanController::class, 'show']);
    Route::put('plans/{id}',   [SubscriptionPlanController::class, 'update']);
    Route::delete('plans/{id}',[SubscriptionPlanController::class, 'destroy']);
});
