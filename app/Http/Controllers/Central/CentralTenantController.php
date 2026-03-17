<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\DatabaseProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class CentralTenantController extends Controller
{
    /**
     * Display tenant registration page.
     */
    public function create(): Response
    {
        return Inertia::render('Central/Tenants/Create');
    }

    /**
     * Handle tenant registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'tenant_name' => ['required', 'string', 'max:255'],
            'tenant_email' => ['required', 'string', 'email', 'max:255', 'unique:tenants,email'],
            'tenant_subdomain' => ['required', 'string', 'max:100', 'unique:tenants,subdomain'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::beginTransaction();

        try {
            // Create admin user
            $adminUser = User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'email_verified_at' => now(),
            ]);

            // Create tenant (in pending state)
            $tenant = Tenant::create([
                'name' => $request->tenant_name,
                'email' => $request->tenant_email,
                'subdomain' => $request->tenant_subdomain,
                'database_name' => 'tenant_' . str_replace('-', '_', $request->tenant_subdomain) . '_' . uniqid(),
                'status' => 'pending', // Requires admin approval
                'trial_ends_at' => now()->addDays(14),
            ]);

            // Assign admin user as tenant owner
            $tenant->owners()->attach($adminUser, ['role' => 'owner']);

            // Assign role to admin user
            $adminUser->assignRole('tenant_owner');

            DB::commit();

            return redirect()->route('central.login')
                ->with('success', 'Tenant registration successful! Please wait for admin approval. You will receive an email once your account is activated.');

        } catch (\Exception $e) {
            DB::rollBack();

            // Cleanup if tenant was created
            if (isset($tenant)) {
                $tenant->delete();
            }

            if (isset($adminUser)) {
                $adminUser->delete();
            }

            return back()
                ->withErrors(['email' => 'Registration failed. Please try again or contact support.'])
                ->withInput();
        }
    }

    /**
     * Display tenant list (for central admin).
     */
    public function index(): Response
    {
        $tenants = Tenant::with('owners')
            ->latest()
            ->paginate(20);

        return Inertia::render('Central/Tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    /**
     * Approve tenant and provision database.
     */
    public function approve(Tenant $tenant): RedirectResponse
    {
        if ($tenant->status !== 'pending') {
            return back()->with('error', 'Tenant is not pending approval.');
        }

        try {
            // Provision tenant database
            $provisioningService = app(DatabaseProvisioningService::class);
            $provisioningService->createDatabaseForTenant($tenant);

            // Seed initial data
            $this->seedTenantData($tenant);

            // Activate tenant
            $tenant->update([
                'status' => 'active',
                'activated_at' => now(),
            ]);

            // Notify admin user (email would be sent here)

            return back()->with('success', 'Tenant approved and activated successfully!');

        } catch (\Exception $e) {
            $tenant->update(['status' => 'suspended']);

            return back()->with('error', 'Failed to approve tenant: ' . $e->getMessage());
        }
    }

    /**
     * Reject tenant registration.
     */
    public function reject(Tenant $tenant, Request $request): RedirectResponse
    {
        if ($tenant->status !== 'pending') {
            return back()->with('error', 'Tenant is not pending approval.');
        }

        $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            // Delete tenant database if exists
            $provisioningService = app(DatabaseProvisioningService::class);
            if ($provisioningService->databaseExists($tenant->database_name)) {
                $provisioningService->deleteDatabaseForTenant($tenant);
            }

            // Delete tenant owners
            $tenant->owners()->detach();

            // Delete tenant
            $tenant->delete();

            return back()->with('success', 'Tenant registration rejected.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reject tenant: ' . $e->getMessage());
        }
    }

    /**
     * Suspend active tenant.
     */
    public function suspend(Tenant $tenant): RedirectResponse
    {
        if ($tenant->status !== 'active') {
            return back()->with('error', 'Tenant is not active.');
        }

        $tenant->update(['status' => 'suspended']);

        return back()->with('success', 'Tenant suspended successfully.');
    }

    /**
     * Reactivate suspended tenant.
     */
    public function reactivate(Tenant $tenant): RedirectResponse
    {
        if ($tenant->status !== 'suspended') {
            return back()->with('error', 'Tenant is not suspended.');
        }

        $tenant->update(['status' => 'active']);

        return back()->with('success', 'Tenant reactivated successfully.');
    }

    /**
     * Seed initial data for tenant.
     */
    protected function seedTenantData(Tenant $tenant): void
    {
        // This will be called after database provisioning
        // The TenantDatabaseSeeder will run automatically
        // Additional custom seeding can be done here
    }
}
