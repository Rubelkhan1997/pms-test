<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenants = Tenant::on('landlord')
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('search'), fn ($q, $s) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('domain', 'like', "%{$s}%")
            ))
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'status'  => 1,
            'data'    => [
                'items'      => $tenants->items(),
                'pagination' => [
                    'current_page' => $tenants->currentPage(),
                    'per_page'     => $tenants->perPage(),
                    'total'        => $tenants->total(),
                    'last_page'    => $tenants->lastPage(),
                ],
            ],
            'message' => 'Tenants fetched successfully',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:200'],
            'domain'        => ['required', 'string', 'max:255', 'unique:landlord.tenants,domain'],
            'status'        => ['sometimes', 'in:pending,active,trial,suspended'],
            'contact_name'  => ['nullable', 'string', 'max:200'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'plan_id'       => ['nullable', 'integer', 'exists:landlord.subscription_plans,id'],
        ]);

        $tenant = Tenant::create($validated);

        return response()->json([
            'status'  => 1,
            'data'    => $tenant,
            'message' => 'Tenant created and provisioned successfully',
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $tenant = Tenant::on('landlord')->findOrFail($id);

        return response()->json([
            'status'  => 1,
            'data'    => $tenant,
            'message' => 'Tenant fetched successfully',
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $tenant = Tenant::on('landlord')->findOrFail($id);

        $validated = $request->validate([
            'name'          => ['sometimes', 'string', 'max:200'],
            'contact_name'  => ['nullable', 'string', 'max:200'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'plan_id'       => ['nullable', 'integer', 'exists:landlord.subscription_plans,id'],
        ]);

        $tenant->update($validated);

        return response()->json([
            'status'  => 1,
            'data'    => $tenant->fresh(),
            'message' => 'Tenant updated successfully',
        ]);
    }

    public function suspend(int $id): JsonResponse
    {
        $tenant = Tenant::on('landlord')->findOrFail($id);
        $tenant->update(['status' => 'suspended']);

        return response()->json([
            'status'  => 1,
            'data'    => $tenant->fresh(),
            'message' => 'Tenant suspended',
        ]);
    }

    public function activate(int $id): JsonResponse
    {
        $tenant = Tenant::on('landlord')->findOrFail($id);
        $tenant->update(['status' => 'active']);

        return response()->json([
            'status'  => 1,
            'data'    => $tenant->fresh(),
            'message' => 'Tenant activated',
        ]);
    }
}
