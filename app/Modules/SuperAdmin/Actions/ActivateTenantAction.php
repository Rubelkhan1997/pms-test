<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Actions;

use App\Models\Tenant;

class ActivateTenantAction
{
    public function execute(Tenant $tenant): Tenant
    {
        $tenant->update(['status' => 'active']);
        return $tenant->fresh();
    }
}