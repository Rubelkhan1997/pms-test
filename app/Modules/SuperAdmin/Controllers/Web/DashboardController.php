<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    use AuthorizesRequests;

    public function index(): Response
    {
        return Inertia::render('SuperAdmin/Dashboard/Index', [
            'stats' => [
                'totalActiveTenants'    => \App\Models\Tenant::on('landlord')->where('status', 'active')->count(),
                'totalActiveProperties' => 0,
                'pendingInvoices'       => 0,
                'trialExpiringSoon'    => 0,
            ],
        ]);
    }
}
