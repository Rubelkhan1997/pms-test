<?php

declare(strict_types=1);

namespace App\Tenancy\Actions;

use Spatie\Multitenancy\Actions\MigrateTenantAction as BaseMigrateTenantAction;
use Spatie\Multitenancy\Models\Tenant;
use Illuminate\Support\Facades\Artisan;

class MigrateTenantAction extends BaseMigrateTenantAction
{
    public function execute(Tenant $tenant): void
    {
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path'     => 'database/migrations/tenant',
            '--force'    => true,
        ]);
    }
}
