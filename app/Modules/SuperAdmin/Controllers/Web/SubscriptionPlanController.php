<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Response;

class SubscriptionPlanController extends Controller
{
    public function index(): Response
    {
        return inertia('SuperAdmin/SubscriptionPlans/Index');
    }

    public function create(): Response
    {
        return inertia('SuperAdmin/SubscriptionPlans/Create');
    }

    public function edit(int $id): Response
    {
        return inertia('SuperAdmin/SubscriptionPlans/Create', ['id' => $id]);
    }
}
