<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\SubscriptionPlan;

uses(RefreshDatabase::class);

it('debug: check route registration', function (): void {
    // Check if the route is registered
    $routes = \Route::getRoutes();
    $found = false;
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'admin/plans')) {
            echo "Route found: " . implode('|', $route->methods()) . ' ' . $route->uri() . PHP_EOL;
            echo "Domain: " . ($route->domain() ?: 'any') . PHP_EOL;
            $found = true;
        }
    }
    expect($found)->toBeTrue();
});

it('creates a subscription plan', function (): void {
    $payload = [
        'name'            => 'Starter',
        'slug'            => 'starter',
        'property_limit'  => 1,
        'room_limit'      => 30,
        'price_monthly'   => 49.99,
        'price_annual'    => 499.00,
        'trial_enabled'   => true,
        'trial_days'      => 14,
        'modules_included'=> ['pms', 'housekeeping'],
    ];

    $response = $this->withServerVariables(['HTTP_HOST' => config('app.admin_domain')])
         ->postJson('/api/v1/admin/plans', $payload);
    
    echo "Status: " . $response->getStatusCode() . PHP_EOL;
    echo "Content: " . $response->getContent() . PHP_EOL;
    
    $response->assertCreated();
});
