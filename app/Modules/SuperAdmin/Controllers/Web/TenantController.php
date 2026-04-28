<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Data\TenantData;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(): Response
    {
        $tenants = Tenant::on('landlord')
            ->latest('id')
            ->get();

        return inertia('SuperAdmin/Tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    public function create(): Response
    {
        return inertia('SuperAdmin/Tenants/Create');
    }

    public function store(): RedirectResponse
    {
        $validated = request()->validate([
            'name' => ['required', 'string', 'max:200'],
            'domain' => ['required', 'string', 'max:255', 'unique:landlord.tenants,domain'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $data = new TenantData(
            name: $validated['name'],
            domain: $validated['domain'],
            status: 'active',
            contact_email: $validated['email'],
            contact_name: null,
            contact_phone: null,
            plan_id: null,
        );

        $tenant = app(\App\Modules\SuperAdmin\Actions\CreateTenantAction::class)->execute($data);

        return redirect()->route('super-admin.tenants.index')
            ->with('success', 'Tenant created successfully.');
    }

    public function show(int $id): Response
    {
        $tenant = Tenant::on('landlord')->findOrFail($id);

        return inertia('SuperAdmin/Tenants/Show', [
            'tenant' => $tenant,
        ]);
    }

    public function edit(int $id): Response
    {
        $tenant = Tenant::on('landlord')->findOrFail($id);

        return inertia('SuperAdmin/Tenants/Create', [
            'tenant' => $tenant,
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        $tenant = Tenant::on('landlord')->findOrFail($id);

        $validated = request()->validate([
            'name' => ['sometimes', 'required', 'string', 'max:200'],
            'email' => ['sometimes', 'required', 'email', 'max:255'],
        ]);

        $tenant->update([
            'name' => $validated['name'] ?? $tenant->name,
            'contact_email' => $validated['email'] ?? $tenant->contact_email,
        ]);

        return redirect()->route('super-admin.tenants.index')
            ->with('success', 'Tenant updated successfully.');
    }
}
