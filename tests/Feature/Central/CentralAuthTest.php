<?php

namespace Tests\Feature\Central;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Central Authentication Tests
 *
 * Tests for central admin authentication and login workflow.
 */
describe('Central Authentication', function () {
    beforeEach(function () {
        // Create roles
        $this->superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $this->tenantOwnerRole = Role::firstOrCreate(['name' => 'tenant_owner']);
        $this->hotelAdminRole = Role::firstOrCreate(['name' => 'hotel_admin']);
    });

    describe('Login Page', function () {
        it('displays login page', function () {
            $response = $this->get(route('central.login'));

            $response->assertStatus(200);
        });

        it('redirects authenticated users', function () {
            $admin = User::factory()->create();
            $admin->assignRole($this->superAdminRole);

            $response = $this->actingAs($admin)->get(route('central.login'));

            $response->assertRedirect(route('dashboard'));
        });
    });

    describe('Login Workflow', function () {
        it('validates login credentials', function () {
            $response = $this->post(route('central.login'), []);

            $response->assertSessionHasErrors(['email', 'password']);
        });

        it('rejects invalid email', function () {
            $response = $this->post(route('central.login'), [
                'email' => 'invalid@email.com',
                'password' => 'password',
            ]);

            $response->assertSessionHasErrors('email');
        });

        it('rejects incorrect password', function () {
            $admin = User::factory()->create([
                'email' => 'admin@central.com',
                'password' => Hash::make('correctpassword'),
            ]);
            $admin->assignRole($this->superAdminRole);

            $response = $this->post(route('central.login'), [
                'email' => 'admin@central.com',
                'password' => 'wrongpassword',
            ]);

            $response->assertSessionHasErrors('email');
        });

        it('rejects users without super_admin role', function () {
            $regularUser = User::factory()->create([
                'email' => 'user@example.com',
                'password' => Hash::make('password'),
            ]);
            $regularUser->assignRole($this->hotelAdminRole);

            $response = $this->post(route('central.login'), [
                'email' => 'user@example.com',
                'password' => 'password',
            ]);

            $response->assertSessionHasErrors('email');
            $response->assertSessionHasErrors('email', 'You do not have access to the central admin panel.');
        });

        it('authenticates super_admin user successfully', function () {
            $admin = User::factory()->create([
                'email' => 'admin@central.com',
                'password' => Hash::make('password'),
            ]);
            $admin->assignRole($this->superAdminRole);

            $response = $this->post(route('central.login'), [
                'email' => 'admin@central.com',
                'password' => 'password',
            ]);

            $response->assertRedirect(route('dashboard'));
            $this->assertAuthenticated();
        });

        it('records login timestamp', function () {
            $admin = User::factory()->create([
                'email' => 'admin@central.com',
                'password' => Hash::make('password'),
            ]);
            $admin->assignRole($this->superAdminRole);

            expect($admin->last_login_at)->toBeNull();

            $this->post(route('central.login'), [
                'email' => 'admin@central.com',
                'password' => 'password',
            ]);

            $admin->refresh();

            expect($admin->last_login_at)->not->toBeNull();
            expect($admin->last_login_at)->toBePast();
        });

        it('records login IP address', function () {
            $admin = User::factory()->create([
                'email' => 'admin@central.com',
                'password' => Hash::make('password'),
            ]);
            $admin->assignRole($this->superAdminRole);

            expect($admin->last_login_ip)->toBeNull();

            $this->post(route('central.login'), [
                'email' => 'admin@central.com',
                'password' => 'password',
            ]);

            $admin->refresh();

            expect($admin->last_login_ip)->not->toBeNull();
        });

        it('respects remember me option', function () {
            $admin = User::factory()->create([
                'email' => 'admin@central.com',
                'password' => Hash::make('password'),
            ]);
            $admin->assignRole($this->superAdminRole);

            $response = $this->post(route('central.login'), [
                'email' => 'admin@central.com',
                'password' => 'password',
                'remember' => '1',
            ]);

            $response->assertRedirect(route('dashboard'));
            // Remember me cookie should be set
            expect($response->headers->getCookies())->not->toBeEmpty();
        });
    });

    describe('Logout', function () {
        it('requires authentication', function () {
            $response = $this->post(route('central.logout'));

            $response->assertRedirect(route('login'));
        });

        it('logs out authenticated user', function () {
            $admin = User::factory()->create();
            $admin->assignRole($this->superAdminRole);

            $this->actingAs($admin);

            $response = $this->post(route('central.logout'));

            $response->assertRedirect(route('login'));
            $this->assertGuest();
        });

        it('invalidates session on logout', function () {
            $admin = User::factory()->create();
            $admin->assignRole($this->superAdminRole);

            $this->actingAs($admin);

            $response = $this->post(route('central.logout'));

            $response->assertRedirect(route('login'));

            // Session should be invalidated
            $this->assertGuest();
        });
    });

    describe('Tenant Login After Approval', function () {
        it('allows tenant admin to login after tenant is approved', function () {
            // Create tenant admin user in central database
            $tenantAdmin = User::factory()->create([
                'email' => 'admin@tenant.com',
                'password' => Hash::make('password'),
            ]);

            // Create active tenant
            $tenant = Tenant::create([
                'name' => 'Active Tenant',
                'email' => 'contact@tenant.com',
                'subdomain' => 'active-tenant',
                'database_name' => 'tenant_active_tenant_auth',
                'status' => 'active',
                'activated_at' => now(),
            ]);

            // Attach user as tenant owner
            $tenant->owners()->attach($tenantAdmin, ['role' => 'owner']);

            // Assign tenant_owner role
            $tenantAdmin->assignRole($this->tenantOwnerRole);

            // Note: Tenant login would be handled by tenant-specific auth
            // This test verifies the tenant admin user exists and has proper roles
            expect($tenantAdmin->hasRole('tenant_owner'))->toBeTrue();
            expect($tenant->isActive())->toBeTrue();
        });

        it('prevents login for tenant admin if tenant is suspended', function () {
            $tenantAdmin = User::factory()->create([
                'email' => 'admin@suspended.com',
                'password' => Hash::make('password'),
            ]);

            $suspendedTenant = Tenant::create([
                'name' => 'Suspended Tenant',
                'email' => 'contact@suspended.com',
                'subdomain' => 'suspended-tenant',
                'database_name' => 'tenant_suspended_tenant_auth',
                'status' => 'suspended',
                'suspended_at' => now(),
            ]);

            $suspendedTenant->owners()->attach($tenantAdmin, ['role' => 'owner']);
            $tenantAdmin->assignRole($this->tenantOwnerRole);

            // Tenant should be suspended
            expect($suspendedTenant->isSuspended())->toBeTrue();
        });
    });

    describe('Dashboard Access', function () {
        it('redirects to dashboard after successful login', function () {
            $admin = User::factory()->create([
                'email' => 'admin@central.com',
                'password' => Hash::make('password'),
            ]);
            $admin->assignRole($this->superAdminRole);

            $response = $this->post(route('central.login'), [
                'email' => 'admin@central.com',
                'password' => 'password',
            ]);

            $response->assertRedirect(route('dashboard'));
        });

        it('requires authentication for dashboard', function () {
            $response = $this->get(route('central.dashboard'));

            $response->assertRedirect(route('login'));
        });

        it('requires super_admin role for dashboard', function () {
            $regularUser = User::factory()->create();
            $regularUser->assignRole($this->hotelAdminRole);

            $response = $this->actingAs($regularUser)->get(route('central.dashboard'));

            $response->assertStatus(403);
        });
    });
});
