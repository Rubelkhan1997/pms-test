<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            'auth' => [
                'user' => $this->getUserFromToken($request),
            ],

            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ];
    }

    /**
     * Get user from token cookie or session
     */
    private function getUserFromToken(Request $request): ?array
    {
        // Try to get token from cookie
        $token = $request->cookie('auth_token');

        if ($token) {
            // Find user by token using Sanctum
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            
            if ($accessToken && $accessToken->tokenable instanceof \App\Models\User) {
                $user = $accessToken->tokenable;
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ?? 'staff',
                    'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                ];
            }
        }

        // Fallback to session-based auth
        if ($request->user()) {
            return [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->role ?? 'staff',
                'email_verified_at' => $request->user()->email_verified_at?->toIso8601String(),
            ];
        }

        return null;
    }
}
