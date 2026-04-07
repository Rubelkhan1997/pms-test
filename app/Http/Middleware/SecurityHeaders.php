<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Add baseline security headers to all application responses.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');
        $response->headers->set(
            'Content-Security-Policy-Report-Only',
            $this->build_csp_policy()
        );

        if (app()->environment('production') || $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        $response->headers->remove('X-Powered-By');

        return $response;
    }

    /**
     * Build Content Security Policy rules.
     */
    private function build_csp_policy(): string
    {
        $script_src = ["'self'"];
        $style_src = ["'self'", "'unsafe-inline'", 'https://fonts.bunny.net'];
        $font_src = ["'self'", 'data:', 'https://fonts.bunny.net'];
        $connect_src = ["'self'"];

        if (app()->environment('local')) {
            $script_src[] = 'http://localhost:5173';
            $style_src[] = 'http://localhost:5173';
            $connect_src[] = 'http://localhost:5173';
            $connect_src[] = 'ws://localhost:5173';
        }

        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
            "object-src 'none'",
            "form-action 'self'",
            'img-src ' . implode(' ', ["'self'", 'data:', 'blob:']),
            'script-src ' . implode(' ', array_unique($script_src)),
            'style-src ' . implode(' ', array_unique($style_src)),
            'font-src ' . implode(' ', array_unique($font_src)),
            'connect-src ' . implode(' ', array_unique($connect_src)),
        ];

        return implode('; ', $directives) . ';';
    }
}
