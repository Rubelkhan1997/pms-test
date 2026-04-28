<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Actions;

use App\Models\Tenant;

class SuspendTenantAction
{
    public function execute(Tenant $tenant): Tenant
    {
        $tenant->update(['status' => 'suspended']);
        return $tenant->fresh();
    }
}