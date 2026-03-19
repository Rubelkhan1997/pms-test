<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\DatabaseProvisioningService;
use App\TenantFinder\SubdomainTenantFinder;
use Closure;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Actions\MakeTenantCurrentAction;

class ForceTenantFromSubdomain
{
    /**
     * Resolve tenant by subdomain and switch the DB connection.
     */
    public function handle(Request $request, Closure $next)
    {
        $tenant = app(SubdomainTenantFinder::class)->findForRequest($request);

        if ($tenant) {
            app(DatabaseProvisioningService::class)->setTenantConnection($tenant);
            app(MakeTenantCurrentAction::class)->execute($tenant);
        }

        return $next($request);
    }
}
