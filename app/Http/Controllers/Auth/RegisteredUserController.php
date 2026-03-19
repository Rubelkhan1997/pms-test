<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use App\Services\DatabaseProvisioningService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'company_name' => $request->company_name,
                'phone' => $request->phone,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            // Create tenant (hotel) for the user
            $tenant = Tenant::create([
                'name' => $request->company_name ?? $request->name . "'s Hotel",
                'email' => $request->email,
                'subdomain' => $this->generateSubdomain($request->name),
                'status' => 'pending',
                'trial_ends_at' => now()->addDays(14), // 14-day trial
            ]);

            // Assign user as owner of tenant
            $tenant->owners()->attach($user, ['role' => 'owner']);

            // Provision tenant database
            $provisioningService = app(DatabaseProvisioningService::class);
            $provisioningService->createDatabaseForTenant($tenant);
            $provisioningService->createTenantAdminUser($tenant, [
                'name' => $user->name,
                'email' => $user->email,
                'password' => $request->password,
            ]);

            // Activate tenant
            $tenant->activate();

            // Assign default role
            $user->assignRole('tenant_owner');

            event(new Registered($user));

            // Log in the user
            Auth::login($user);

            // Set current tenant
            $user->switchTenant($tenant);

            DB::commit();

            return redirect()->route('dashboard')
                ->with('success', 'Account created successfully! Your 14-day trial has started.');

        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up if tenant was created
            if (isset($tenant)) {
                $provisioningService = app(DatabaseProvisioningService::class);
                $provisioningService->deleteDatabaseForTenant($tenant);
                $tenant->delete();
            }

            // Clean up user
            if (isset($user)) {
                $user->delete();
            }

            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Registration failed. Please try again or contact support.',
            ]);
        }
    }

    /**
     * Generate unique subdomain from name.
     */
    protected function generateSubdomain(string $name): string
    {
        $subdomain = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $name));
        $subdomain = substr($subdomain, 0, 20);
        
        // Ensure uniqueness
        $counter = 1;
        $originalSubdomain = $subdomain;
        
        while (Tenant::where('subdomain', $subdomain)->exists()) {
            $subdomain = $originalSubdomain . $counter;
            $counter++;
        }
        
        return $subdomain;
    }
}
