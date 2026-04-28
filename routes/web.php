<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin Routes  (admin.pms.test)
|--------------------------------------------------------------------------
*/
Route::domain(config('app.admin_domain', 'admin.pms.test'))
    ->middleware(['super.admin.only'])
    ->group(base_path('routes/super-admin.php'));

/*
|--------------------------------------------------------------------------
| Tenant PMS Routes  ({tenant}.pms.test)
|--------------------------------------------------------------------------
*/
Route::middleware(['needs.tenant'])->group(base_path('routes/tenant.php'));



