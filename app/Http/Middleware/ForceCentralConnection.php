<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Actions\ForgetCurrentTenantAction;

class ForceCentralConnection
{
    /**
     * Force the central (landlord) database connection for central routes.
     */
    public function handle(Request $request, Closure $next)
    {
        $connection = config('central.connection', 'central');
        $database = config('central.database', config("database.connections.{$connection}.database"));

        config(['database.default' => $connection]);
        config(["database.connections.{$connection}.database" => $database]);

        DB::purge($connection);
        DB::setDefaultConnection($connection);
        DB::reconnect($connection);

        if (class_exists(ForgetCurrentTenantAction::class)) {
            $tenantKey = config('multitenancy.current_tenant_container_key', 'currentTenant');
            if (app()->bound($tenantKey)) {
                $currentTenant = app($tenantKey);
                app(ForgetCurrentTenantAction::class)->execute($currentTenant);
            }
        }

        return $next($request);
    }
}
