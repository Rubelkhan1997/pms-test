<?php

namespace Tests\Feature\Central;

use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Tenant Registration Tests
 *
 * Tests for the tenant registration workflow.
 */
describe('Tenant Registration', function () {
    beforeEach(function () {
        // Create super_admin role for central admin
        $this->superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $this->tenantOwnerRole = Role::firstOrCreate(['name' => 'tenant_owner']);

        // Create a central admin user
        $this->centralAdmin = User::factory()->create([
            'email' => 'admin@central.com',
        ]);
        $this->centralAdmin->assignRole($this->superAdminRole);
    });

    it('displays tenant registration page', function () {
        $response = $this->get(route('central.register'));

        $response->assertStatus(200);
    });

    it('validates tenant registration input', function () {
        $response = $this->post(route('central.register'), []);

        $response->assertSessionHasErrors([
            'tenant_name',
            'tenant_email',
            'tenant_subdomain',
            'admin_name',
            'admin_email',
            'admin_password',
        ]);
    });

    it('validates tenant email is unique', function () {
        Tenant::create([
            'name' => 'Existing Tenant',
            'email' => 'existing@tenant.com',
            'subdomain' => 'existing-tenant',
            'database_name' => 'tenant_existing',
            'status' => 'pending',
        ]);

        $response = $this->post(route('central.register'), [
            'tenant_name' => 'New Tenant',
            'tenant_email' => 'existing@tenant.com',
            'tenant_subdomain' => 'new-tenant',
            'admin_name' => 'Admin User',
            'admin_email' => 'admin@example.com',
            'admin_password' => 'password',
            'admin_password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('tenant_email');
    });

    it('validates tenant subdomain is unique', function () {
        Tenant::create([
            'name' => 'Existing Tenant',
            'email' => 'existing@tenant.com',
            'subdomain' => 'existing-tenant',
            'database_name' => 'tenant_existing',
            'status' => 'pending',
        ]);

        $response = $this->post(route('central.register'), [
            'tenant_name' => 'New Tenant',
            'tenant_email' => 'new@tenant.com',
            'tenant_subdomain' => 'existing-tenant',
            'admin_name' => 'Admin User',
            'admin_email' => 'admin@example.com',
            'admin_password' => 'password',
            'admin_password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('tenant_subdomain');
    });

    it('validates admin email is unique', function () {
        User::factory()->create(['email' => 'existing@admin.com']);

        $response = $this->post(route('central.register'), [
            'tenant_name' => 'New Tenant',
            'tenant_email' => 'new@tenant.com',
            'tenant_subdomain' => 'new-tenant',
            'admin_name' => 'Admin User',
            'admin_email' => 'existing@admin.com',
            'admin_password' => 'password',
            'admin_password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('admin_email');
    });

    it('validates password confirmation', function () {
        $response = $this->post(route('central.register'), [
            'tenant_name' => 'New Tenant',
            'tenant_email' => 'new@tenant.com',
            'tenant_subdomain' => 'new-tenant',
            'admin_name' => 'Admin User',
            'admin_email' => 'admin@example.com',
            'admin_password' => 'password',
            'admin_password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors('admin_password');
    });

    it('creates tenant with pending status on registration', function () {
        $tenantData = [
            'tenant_name' => 'Test Hotel',
            'tenant_email' => 'contact@testhotel.com',
            'tenant_subdomain' => 'test-hotel',
            'admin_name' => 'John Doe',
            'admin_email' => 'john@testhotel.com',
            'admin_password' => 'securepassword123',
            'admin_password_confirmation' => 'securepassword123',
        ];

        $response = $this->post(route('central.register'), $tenantData);

        $response->assertRedirect(route('central.login'));
        $response->assertSessionHas('success', 'Tenant registration successful! Please wait for admin approval. You will receive an email once your account is activated.');

        $this->assertDatabaseHas('tenants', [
            'name' => 'Test Hotel',
            'email' => 'contact@testhotel.com',
            'subdomain' => 'test-hotel',
            'status' => 'pending',
        ]);
    });

    it('creates admin user on registration', function () {
        $tenantData = [
            'tenant_name' => 'Test Hotel',
            'tenant_email' => 'contact@testhotel.com',
            'tenant_subdomain' => 'test-hotel',
            'admin_name' => 'John Doe',
            'admin_email' => 'john@testhotel.com',
            'admin_password' => 'securepassword123',
            'admin_password_confirmation' => 'securepassword123',
        ];

        $this->post(route('central.register'), $tenantData);

        $adminUser = User::where('email', 'john@testhotel.com')->first();

        expect($adminUser)->not->toBeNull();
        expect($adminUser->name)->toBe('John Doe');
        expect($adminUser->email)->toBe('john@testhotel.com');
    });

    it('assigns tenant_owner role to admin user', function () {
        $tenantData = [
            'tenant_name' => 'Test Hotel',
            'tenant_email' => 'contact@testhotel.com',
            'tenant_subdomain' => 'test-hotel',
            'admin_name' => 'John Doe',
            'admin_email' => 'john@testhotel.com',
            'admin_password' => 'securepassword123',
            'admin_password_confirmation' => 'securepassword123',
        ];

        $this->post(route('central.register'), $tenantData);

        $adminUser = User::where('email', 'john@testhotel.com')->first();
        $tenant = Tenant::where('email', 'contact@testhotel.com')->first();

        expect($adminUser->hasRole('tenant_owner'))->toBeTrue();

        // Verify the user is attached as tenant owner
        expect($tenant->owners)->toContain($adminUser);
    });

    it('sets trial period for new tenant', function () {
        $tenantData = [
            'tenant_name' => 'Test Hotel',
            'tenant_email' => 'contact@testhotel.com',
            'tenant_subdomain' => 'test-hotel',
            'admin_name' => 'John Doe',
            'admin_email' => 'john@testhotel.com',
            'admin_password' => 'securepassword123',
            'admin_password_confirmation' => 'securepassword123',
        ];

        $this->post(route('central.register'), $tenantData);

        $tenant = Tenant::where('email', 'contact@testhotel.com')->first();

        expect($tenant->trial_ends_at)->toBeFuture();
        expect($tenant->trial_ends_at->diffInDays(now()))->toBeGreaterThanOrEqual(13);
        expect($tenant->trial_ends_at->diffInDays(now()))->toBeLessThanOrEqual(15);
    });

    it('generates unique database name for tenant', function () {
        $tenantData = [
            'tenant_name' => 'Test Hotel',
            'tenant_email' => 'contact@testhotel.com',
            'tenant_subdomain' => 'test-hotel',
            'admin_name' => 'John Doe',
            'admin_email' => 'john@testhotel.com',
            'admin_password' => 'securepassword123',
            'admin_password_confirmation' => 'securepassword123',
        ];

        $this->post(route('central.register'), $tenantData);

        $tenant = Tenant::where('email', 'contact@testhotel.com')->first();

        expect($tenant->database_name)->toStartWith('tenant_test_hotel_');
        expect($tenant->database_name)->not->toBeNull();
    });
});
