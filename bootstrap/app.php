<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',

        then: function (): void {
            Route::domain(config('app.admin_domain', 'admin.pms.test'))
                ->middleware(['super.admin.only', 'api'])
                ->prefix('api')
                ->group(base_path('routes/super-admin-api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(['auth_token']);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // API routes don't need CSRF (token-based auth)
        $middleware->alias([
            'auth.token' => \App\Modules\Auth\Middleware\AuthenticateByToken::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Inertia middleware for sharing props
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'needs.tenant'              => \App\Http\Middleware\NeedsTenant::class,
            'super.admin.only'          => \App\Http\Middleware\SuperAdminOnly::class,
            'ensure.subscription.active'=> \App\Http\Middleware\EnsureSubscriptionActive::class,
            'ensure.property.onboarded' => \App\Http\Middleware\EnsurePropertyOnboarded::class,
            // Keep existing ones:
            'auth.token'              => \App\Modules\Auth\Middleware\AuthenticateByToken::class,
            'permission'              => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role'                   => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission'       => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Spatie's middleware to automatically resolve tenant on every request
        $middleware->web(append: [
            \Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession::class,
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
