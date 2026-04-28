<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Requests\StoreSubscriptionPlanRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Response;
use App\Models\SubscriptionPlan;

class SubscriptionPlanController extends Controller
{
    public function index(): Response
    {
        $plans = SubscriptionPlan::on('landlord')
            ->latest()
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'price' => $plan->price_monthly,
                    'billing_cycle' => 'monthly',
                    'status' => $plan->is_active ? 'active' : 'inactive',
                    'created_at' => $plan->created_at,
                ];
            });

        return inertia('SuperAdmin/SubscriptionPlans/Index', [
            'plans' => $plans,
        ]);
    }

    public function create(): Response
    {
        return inertia('SuperAdmin/SubscriptionPlans/Create');
    }

    public function store(StoreSubscriptionPlanRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $planData = [
            'name' => $data['name'],
            'slug' => \Illuminate\Support\Str::slug($data['name']),
            'price_monthly' => $data['billing_cycle'] === 'monthly' ? $data['price'] : 0,
            'price_annual' => $data['billing_cycle'] === 'yearly' ? $data['price'] : 0,
            'is_active' => $data['status'] === 'active',
            'modules_included' => isset($data['features']) ? array_filter(
                explode("\n", $data['features']),
                fn ($feature) => trim($feature) !== ''
            ) : [],
        ];

        SubscriptionPlan::on('landlord')->create($planData);

        return redirect()->route('super-admin.plans.index')
            ->with('success', 'Subscription plan created successfully.');
    }

    public function edit(int $id): Response
    {
        $plan = SubscriptionPlan::on('landlord')->findOrFail($id);

        return inertia('SuperAdmin/SubscriptionPlans/Create', [
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'price' => $plan->price_monthly ?: $plan->price_annual,
                'billing_cycle' => $plan->price_monthly ? 'monthly' : 'yearly',
                'features' => is_array($plan->modules_included) ? implode("\n", $plan->modules_included) : '',
                'status' => $plan->is_active ? 'active' : 'inactive',
            ],
        ]);
    }

    public function update(StoreSubscriptionPlanRequest $request, int $id): RedirectResponse
    {
        $plan = SubscriptionPlan::on('landlord')->findOrFail($id);
        $data = $request->validated();

        $planData = [
            'name' => $data['name'],
            'price_monthly' => $data['billing_cycle'] === 'monthly' ? $data['price'] : $plan->price_monthly,
            'price_annual' => $data['billing_cycle'] === 'yearly' ? $data['price'] : $plan->price_annual,
            'is_active' => $data['status'] === 'active',
        ];

        if (isset($data['features'])) {
            $planData['modules_included'] = array_filter(
                explode("\n", $data['features']),
                fn ($feature) => trim($feature) !== ''
            );
        }

        $plan->update($planData);

        return redirect()->route('super-admin.plans.index')
            ->with('success', 'Subscription plan updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $plan = SubscriptionPlan::on('landlord')->findOrFail($id);
        $plan->delete();

        return redirect()->route('super-admin.plans.index')
            ->with('success', 'Subscription plan deleted successfully.');
    }
}
