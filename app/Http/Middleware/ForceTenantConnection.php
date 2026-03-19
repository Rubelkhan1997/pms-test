<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\DatabaseProvisioningService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ForceTenantConnection
{
    /**
     * Force tenant database connection for authenticated tenant routes.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Ensure central connection to resolve the current tenant.
        $centralConnection = config('central.connection', 'central');
        $centralDatabase = config('central.database', config("database.connections.{$centralConnection}.database"));

        config(['database.default' => $centralConnection]);
        config(["database.connections.{$centralConnection}.database" => $centralDatabase]);
        DB::purge($centralConnection);
        DB::setDefaultConnection($centralConnection);
        DB::reconnect($centralConnection);

        $tenant = $user->current_tenant;

        if (!$tenant) {
            abort(403, 'No tenant is selected for this session.');
        }

        // Switch default connection to the tenant database.
        app(DatabaseProvisioningService::class)->setTenantConnection($tenant);

        return $next($request);
    }
}
