<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsurePropertyOnboarded
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip the check if we're already on the onboarding route
        if ($request->routeIs('onboarding.*')) {
            return $next($request);
        }

        $propertyCount = DB::table('properties')->count();

        if ($propertyCount === 0) {
            return redirect('/onboarding/property/create');
        }

        return $next($request);
    }
}
