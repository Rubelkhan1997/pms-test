<?php

declare(strict_types=1);

namespace App\TenantFinder;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class SubdomainTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?IsTenant
    {
        $host = $request->getHost();

        if (!$host || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        $centralHost = parse_url(config('app.url'), PHP_URL_HOST);

        if (!$centralHost || Str::endsWith($host, $centralHost) === false) {
            return null;
        }

        if ($host === $centralHost) {
            return null;
        }

        $subdomain = Str::before($host, '.' . $centralHost);
        $subdomain = trim($subdomain);

        if ($subdomain === '' || in_array($subdomain, ['www', 'central'], true)) {
            return null;
        }

        $centralConnection = config('central.connection', 'central');

        return Tenant::on($centralConnection)
            ->where('subdomain', $subdomain)
            ->first();
    }
}
