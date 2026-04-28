<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NeedsTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Tenant::checkCurrent()) {
            abort(404, 'Tenant not found.');
        }

        return $next($request);
    }
}
