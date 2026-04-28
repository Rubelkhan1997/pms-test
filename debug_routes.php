<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'Admin domain: ' . config('app.admin_domain') . PHP_EOL;

$routes = \Route::getRoutes();
$count = 0;
foreach ($routes as $route) {
    $uri = $route->uri();
    $domain = $route->domain();
    if (str_contains($uri, 'admin') || str_contains($uri, 'plans') || str_contains($uri, 'tenants')) {
        echo implode('|', $route->methods()) . ' ' . $uri . ' (domain: ' . ($domain ?: 'any') . ')' . PHP_EOL;
        $count++;
    }
}
echo 'Total matching routes: ' . $count . PHP_EOL;
