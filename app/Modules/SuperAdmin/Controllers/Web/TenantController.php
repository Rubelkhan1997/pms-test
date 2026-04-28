<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(): Response
    {
        return inertia('SuperAdmin/Tenants/Index');
    }

    public function create(): Response
    {
        return inertia('SuperAdmin/Tenants/Create');
    }

    public function show(int $id): Response
    {
        return inertia('SuperAdmin/Tenants/Show', ['id' => $id]);
    }

    public function edit(int $id): Response
    {
        return inertia('SuperAdmin/Tenants/Create', ['id' => $id]);
    }
}
