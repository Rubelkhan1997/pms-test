<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: static function (): void {
            Route::middleware(['web', 'super.admin.only'])
                ->domain(config('app.admin_domain', 'admin.pms.test'))
                ->group(base_path('routes/super-admin.php'));

            Route::middleware(['web', 'needs.tenant'])
                ->domain('{tenant}.'.config('app.tenant_base_domain', 'pms.test'))
                ->group(base_path('routes/tenant.php'));

            Route::middleware('api')
                ->domain(config('app.admin_domain', 'admin.pms.test'))
                ->group(base_path('routes/super-admin-api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request): string {
            $adminDomain = config('app.admin_domain', 'admin.pms.test');
            if ($request->getHost() === $adminDomain) {
                return route('super-admin.dashboard');
            }
            return '/dashboard';
        });

        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request): string {
            $adminDomain = config('app.admin_domain', 'admin.pms.test');
            if ($request->getHost() === $adminDomain) {
                return route('super-admin.login');
            }
            return '/login';
        });

        $middleware->encryptCookies(['auth_token']);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->alias([
            'auth.token'                => \App\Modules\Auth\Middleware\AuthenticateByToken::class,
            'needs.tenant'              => \App\Http\Middleware\NeedsTenant::class,
            'super.admin.only'          => \App\Http\Middleware\SuperAdminOnly::class,
            'ensure.subscription.active'=> \App\Http\Middleware\EnsureSubscriptionActive::class,
            'ensure.property.onboarded' => \App\Http\Middleware\EnsurePropertyOnboarded::class,
            'permission'                => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role'                      => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission'        => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Spatie's middleware to automatically resolve tenant on every request
        $middleware->web(append: [
            \Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession::class,
            \App\Http\Middleware\NeedsTenant::class,
        ]);

        // Inertia middleware for sharing props
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
    })
    ->withCommands([
        \App\Console\Commands\TenantCreate::class,
        \App\Console\Commands\TenantMigrate::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
