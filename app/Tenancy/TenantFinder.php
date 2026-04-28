<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Spatie\Multitenancy\TenantFinder\TenantFinder as BaseTenantFinder;

class TenantFinder extends BaseTenantFinder
{
    public function findForRequest(Request $request): ?Tenant
    {
        $host = $request->getHost();

        // Admin domain does not resolve to any tenant
        if ($host === config('app.admin_domain', 'admin.pms.test')) {
            return null;
        }

        return Tenant::on('landlord')
            ->where('domain', $host)
            ->where('status', '!=', 'cancelled')
            ->first();
    }
}
