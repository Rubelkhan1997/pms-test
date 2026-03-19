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
use Spatie\Permission\Models\Role;

class CentralTenantController extends Controller
{
    /**
     * Ensure we are using the central database connection.
     */
    protected function ensureCentralConnection(): string
    {
        $connection = env('DB_CONNECTION', config('database.default', 'pgsql'));
        $database = env('DB_DATABASE', config("database.connections.{$connection}.database"));

        config([
            'database.default' => $connection,
            "database.connections.{$connection}.database" => $database,
        ]);

        DB::purge($connection);
        DB::setDefaultConnection($connection);

        return $connection;
    }

    /**
     * Display tenant registration page.
     */
    public function create(): Response
    {
        if (request()->routeIs('central.tenants.create')) {
            return Inertia::render('Central/Tenants/AdminCreate');
        }
        return Inertia::render('Central/Tenants/Create');
    }

    /**
     * Handle tenant registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $centralConnection = $this->ensureCentralConnection();
        $centralDb = DB::connection($centralConnection);

        $request->validate([
            'tenant_name' => ['required', 'string', 'max:255'],
            'tenant_email' => ['required', 'string', 'email', 'max:255', 'unique:tenants,email'],
            'tenant_subdomain' => ['required', 'string', 'max:100', 'unique:tenants,subdomain'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $centralDb->beginTransaction();

        try {
            // Create admin user
            $adminUser = User::on($centralConnection)->create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'email_verified_at' => now(),
            ]);

            // Create tenant (in pending state)
            $tenant = Tenant::on($centralConnection)->create([
                'name' => $request->tenant_name,
                'email' => $request->tenant_email,
                'subdomain' => $request->tenant_subdomain,
                'database_name' => 'tenant_' . str_replace('-', '_', $request->tenant_subdomain) . '_' . uniqid(),
                'status' => 'pending', // Requires admin approval
                'trial_ends_at' => now()->addDays(14),
            ]);

            // Assign admin user as tenant owner
            $tenant->owners()->attach($adminUser, ['role' => 'owner']);

            // Avoid DB cache store dependency during role creation (cache table may not exist yet)
            $originalCacheStore = config('cache.default');
            $originalPermissionCacheStore = config('permission.cache.store');
            config([
                'cache.default' => 'array',
                'permission.cache.store' => 'array',
            ]);

            try {
                // Assign role to admin user (create role if it doesn't exist)
                $role = Role::firstOrCreate(['name' => 'tenant_owner']);
                $adminUser->assignRole($role);
            } finally {
                config([
                    'cache.default' => $originalCacheStore,
                    'permission.cache.store' => $originalPermissionCacheStore,
                ]);
            }

            $centralDb->commit();

            if (request()->routeIs('central.tenants.store')) {
                // If created from admin panel, auto-approve and provision database
                $this->approveTenantImmediately($tenant);
                return redirect()->route('central.tenants.index')
                    ->with('success', 'Tenant created and activated successfully!');
            }

            return redirect()->route('central.login')
                ->with('success', 'Tenant registration successful! Please wait for admin approval. You will receive an email once your account is activated.');

        } catch (\Exception $e) {
            $centralDb->rollBack();

            // Force reconnect to central 'postgres' database
            $currentDb = config('database.connections.pgsql.database');
            config(['database.connections.pgsql.database' => 'postgres']);
            DB::purge('pgsql');
            DB::reconnect('pgsql');
            
            // Use raw SQL to cleanup central database tables only
            if (isset($tenant)) {
                try {
                    DB::table('tenant_owners')->where('tenant_id', $tenant->id)->delete();
                } catch (\Exception $cleanupEx) {
                    \Log::warning('Failed to cleanup tenant_owners: ' . $cleanupEx->getMessage());
                }
                try {
                    $tenant->delete();
                } catch (\Exception $cleanupEx) {
                    \Log::warning('Failed to delete tenant: ' . $cleanupEx->getMessage());
                }
            }

            if (isset($adminUser)) {
                try {
                    $adminUser->delete();
                } catch (\Exception $cleanupEx) {
                    \Log::warning('Failed to delete user: ' . $cleanupEx->getMessage());
                }
            }

            // Don't try to drop the tenant database here - it's already connected
            // The database will be orphaned and can be cleaned up manually if needed
            \Log::error('Tenant registration failed: ' . $e->getMessage(), [
                'tenant' => $tenant->name ?? 'unknown',
                'database' => $tenant->database_name ?? 'unknown',
            ]);

            return back()
                ->withErrors(['general' => 'Registration failed: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display tenant list (for central admin).
     */
    public function index(): Response
    {
        $centralConnection = $this->ensureCentralConnection();

        $tenants = Tenant::on($centralConnection)->with('owners')
            ->latest()
            ->paginate(20);

        return Inertia::render('Central/Tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    /**
     * Approve tenant immediately (for admin-created tenants).
     */
    protected function approveTenantImmediately(Tenant $tenant): void
    {
        try {
            $originalConnection = $this->ensureCentralConnection();
            // Provision tenant database (includes seeding)
            $provisioningService = app(DatabaseProvisioningService::class);
            $provisioningService->createDatabaseForTenant($tenant);
            \DB::setDefaultConnection($originalConnection);
            $tenant->setConnection($originalConnection);

            // Activate tenant
            Tenant::on($originalConnection)
                ->where('id', $tenant->id)
                ->update([
                    'status' => 'active',
                    'activated_at' => now(),
                    'updated_at' => now(),
                ]);
        } catch (\Exception $e) {
            \Log::error('Failed to auto-approve tenant: ' . $e->getMessage());
            throw $e;
        }
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
            $originalConnection = $this->ensureCentralConnection();
            // Provision tenant database
            $provisioningService = app(DatabaseProvisioningService::class);
            $provisioningService->createDatabaseForTenant($tenant);

            // Seed initial data
            $this->seedTenantData($tenant);
            \DB::setDefaultConnection($originalConnection);
            $tenant->setConnection($originalConnection);

            // Activate tenant
            Tenant::on($originalConnection)
                ->where('id', $tenant->id)
                ->update([
                    'status' => 'active',
                    'activated_at' => now(),
                    'updated_at' => now(),
                ]);

            // Notify admin user (email would be sent here)

            return back()->with('success', 'Tenant approved and activated successfully!');

        } catch (\Exception $e) {
            $originalConnection = config('database.default');
            Tenant::on($originalConnection)
                ->where('id', $tenant->id)
                ->update([
                    'status' => 'suspended',
                    'updated_at' => now(),
                ]);

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
        $originalConnection = config('database.default');
        $provisioningService = app(DatabaseProvisioningService::class);
        $provisioningService->seedTenantData($tenant);
        \DB::setDefaultConnection($originalConnection);
    }
}
