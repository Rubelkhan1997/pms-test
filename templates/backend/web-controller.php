<?php

namespace App\Modules\[MODULE]\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\[MODULE]\Models\[MODEL];
use App\Modules\[MODULE]\Services\[SERVICE];
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// FILE: app/Modules/[MODULE]/Controllers/Web/[CONTROLLER]Controller.php

class [CONTROLLER]Controller extends Controller
{
    public function __construct(
        protected [SERVICE] $[service]
    ) {}

    /**
     * Display index page.
     */
    public function index(): Response
    {
        return Inertia::render('[MODULE]/[FEATURE]/Index');
    }

    /**
     * Show create form page.
     */
    public function create(): Response
    {
        return Inertia::render('[MODULE]/[FEATURE]/Create');
    }

    /**
     * Show edit form page.
     */
    public function edit([MODEL] $model): Response
    {
        return Inertia::render('[MODULE]/[FEATURE]/Edit', [
            '[feature]' => $model,
        ]);
    }

    /**
     * Display show page.
     */
    public function show([MODEL] $model): Response
    {
        return Inertia::render('[MODULE]/[FEATURE]/Show', [
            '[feature]' => $model,
        ]);
    }
}
