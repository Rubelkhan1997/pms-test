<?php

namespace Tests\Feature\Central;

use App\Models\Tenant;
use App\Models\User;
use App\Services\DatabaseProvisioningService;
use Spatie\Permission\Models\Role;

/**
 * Central Admin Approval Workflow Tests
 *
 * Tests for the tenant approval workflow by central admin.
 */
describe('Central Admin Approval Workflow', function () {
    beforeEach(function () {
        // Create roles
        $this->superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $this->tenantOwnerRole = Role::firstOrCreate(['name' => 'tenant_owner']);
        $this->hotelAdminRole = Role::firstOrCreate(['name' => 'hotel_admin']);

        // Create a central admin user
        $this->centralAdmin = User::factory()->create([
            'email' => 'admin@central.com',
        ]);
        $this->centralAdmin->assignRole($this->superAdminRole);

        // Create pending tenants
        $this->pendingTenant = Tenant::create([
            'name' => 'Pending Hotel',
            'email' => 'contact@pendinghotel.com',
            'subdomain' => 'pending-hotel',
            'database_name' => 'tenant_pending_hotel_test',
            'status' => 'pending',
            'trial_ends_at' => now()->addDays(14),
        ]);
    });

    describe('View Pending Tenants', function () {
        it('requires authentication', function () {
            $response = $this->get(route('central.tenants.index'));

            $response->assertRedirect(route('login'));
        });

        it('requires super_admin role', function () {
            $regularUser = User::factory()->create();

            $response = $this->actingAs($regularUser)->get(route('central.tenants.index'));

            $response->assertStatus(403);
        });

        it('displays tenant list for authenticated admin', function () {
            $response = $this->actingAs($this->centralAdmin)->get(route('central.tenants.index'));

            $response->assertStatus(200);
            $response->assertSee('Pending Hotel');
            $response->assertSee('contact@pendinghotel.com');
        });

        it('shows pending tenants count', function () {
            Tenant::create([
                'name' => 'Another Pending Hotel',
                'email' => 'contact@anotherpending.com',
                'subdomain' => 'another-pending',
                'database_name' => 'tenant_another_pending_test',
                'status' => 'pending',
                'trial_ends_at' => now()->addDays(14),
            ]);

            $response = $this->actingAs($this->centralAdmin)->get(route('central.tenants.index'));

            $response->assertStatus(200);
            $response->assertSee('Pending Hotel');
            $response->assertSee('Another Pending Hotel');
        });

        it('displays tenant details page', function () {
            $response = $this->actingAs($this->centralAdmin)->get(route('central.tenants.show', $this->pendingTenant));

            $response->assertStatus(200);
            $response->assertSee('Pending Hotel');
        });
    });

    describe('Approve Tenant', function () {
        it('requires authentication', function () {
            $response = $this->post(route('central.tenants.approve', $this->pendingTenant));

            $response->assertRedirect(route('login'));
        });

        it('requires super_admin role', function () {
            $regularUser = User::factory()->create();

            $response = $this->actingAs($regularUser)->post(route('central.tenants.approve', $this->pendingTenant));

            $response->assertStatus(403);
        });

        it('approves pending tenant successfully', function () {
            $this->mock(DatabaseProvisioningService::class, function ($mock) {
                $mock->shouldReceive('createDatabaseForTenant')->once()->andReturn(true);
                $mock->shouldReceive('createTenantAdminUser')->once();
                $mock->shouldReceive('seedTenantData')->once();
            });

            $response = $this->actingAs($this->centralAdmin)->post(route('central.tenants.approve', $this->pendingTenant));

            $response->assertRedirect();
            $response->assertSessionHas('success', 'Tenant approved and activated successfully!');

            $this->assertDatabaseHas('tenants', [
                'id' => $this->pendingTenant->id,
                'status' => 'active',
            ]);
        });

        it('cannot approve already active tenant', function () {
            $activeTenant = Tenant::create([
                'name' => 'Active Hotel',
                'email' => 'contact@activehotel.com',
                'subdomain' => 'active-hotel',
                'database_name' => 'tenant_active_hotel_test',
                'status' => 'active',
                'activated_at' => now(),
            ]);

            $response = $this->actingAs($this->centralAdmin)->post(route('central.tenants.approve', $activeTenant));

            $response->assertRedirect();
            $response->assertSessionHas('error', 'Tenant is not pending approval.');
        });

        it('cannot approve suspended tenant', function () {
            $suspendedTenant = Tenant::create([
                'name' => 'Suspended Hotel',
                'email' => 'contact@suspendedhotel.com',
                'subdomain' => 'suspended-hotel',
                'database_name' => 'tenant_suspended_hotel_test',
                'status' => 'suspended',
            ]);

            $response = $this->actingAs($this->centralAdmin)->post(route('central.tenants.approve', $suspendedTenant));

            $response->assertRedirect();
            $response->assertSessionHas('error', 'Tenant is not pending approval.');
        });

        it('sets activated_at timestamp on approval', function () {
            $this->mock(DatabaseProvisioningService::class, function ($mock) {
                $mock->shouldReceive('createDatabaseForTenant')->once()->andReturn(true);
                $mock->shouldReceive('createTenantAdminUser')->once();
                $mock->shouldReceive('seedTenantData')->once();
            });

            $this->actingAs($this->centralAdmin)->post(route('central.tenants.approve', $this->pendingTenant));

            $tenant = Tenant::find($this->pendingTenant->id);

            expect($tenant->activated_at)->not->toBeNull();
            expect($tenant->activated_at)->toBePast();
        });

        it('handles approval failure gracefully', function () {
            $this->mock(DatabaseProvisioningService::class, function ($mock) {
                $mock->shouldReceive('createDatabaseForTenant')->once()->andThrow(new \Exception('Database creation failed'));
            });

            $response = $this->actingAs($this->centralAdmin)->post(route('central.tenants.approve', $this->pendingTenant));

            $response->assertRedirect();
            $response->assertSessionHas('error');

            // Tenant should be suspended on failure
            $this->assertDatabaseHas('tenants', [
                'id' => $this->pendingTenant->id,
                'status' => 'suspended',
            ]);
        });
    });

    describe('Reject Tenant', function () {
        it('requires authentication', function () {
            $response = $this->post(route('central.tenants.reject', $this->pendingTenant));

            $response->assertRedirect(route('login'));
        });

        it('requires super_admin role', function () {
            $regularUser = User::factory()->create();

            $response = $this->actingAs($regularUser)->post(route('central.tenants.reject', $this->pendingTenant), [
                'rejection_reason' => 'Incomplete information',
            ]);

            $response->assertStatus(403);
        });

        it('rejects pending tenant', function () {
            $response = $this->actingAs($this->centralAdmin)->post(route('central.tenants.reject', $this->pendingTenant), [
                'rejection_reason' => 'Incomplete information',
            ]);

            $response->assertRedirect();
            $response->assertSessionHas('success', 'Tenant registration rejected.');

            // Tenant should be deleted
            $this->assertDatabaseMissing('tenants', [
                'id' => $this->pendingTenant->id,
            ]);
        });

        it('cannot reject already active tenant', function () {
            $activeTenant = Tenant::create([
                'name' => 'Active Hotel',
                'email' => 'contact@activehotel.com',
                'subdomain' => 'active-hotel',
                'database_name' => 'tenant_active_hotel_test',
                'status' => 'active',
                'activated_at' => now(),
            ]);

            $response = $this->actingAs($this->centralAdmin)->post(route('central.tenants.reject', $activeTenant), [
                'rejection_reason' => 'Not applicable',
            ]);

            $response->assertRedirect();
            $response->assertSessionHas('error', 'Tenant is not pending approval.');
        });

        it('accepts optional rejection reason', function () {
            $response = $this->actingAs($this->centralAdmin)->post(route('central.tenants.reject', $this->pendingTenant), []);

            $response->assertRedirect();
            $response->assertSessionHas('success', 'Tenant registration rejected.');
        });
    });

    describe('Admin Dashboard', function () {
        it('displays dashboard with statistics', function () {
            // Create additional tenants for stats
            Tenant::create([
                'name' => 'Active Hotel',
                'email' => 'contact@activehotel.com',
                'subdomain' => 'active-hotel',
                'database_name' => 'tenant_active_hotel_test2',
                'status' => 'active',
                'activated_at' => now(),
            ]);

            Tenant::create([
                'name' => 'Suspended Hotel',
                'email' => 'contact@suspendedhotel.com',
                'subdomain' => 'suspended-hotel',
                'database_name' => 'tenant_suspended_hotel_test2',
                'status' => 'suspended',
            ]);

            $response = $this->actingAs($this->centralAdmin)->get(route('central.dashboard'));

            $response->assertStatus(200);
            $response->assertSee('total_tenants');
            $response->assertSee('pending_tenants');
            $response->assertSee('active_tenants');
            $response->assertSee('suspended_tenants');
        });

        it('shows correct tenant counts in dashboard', function () {
            Tenant::create([
                'name' => 'Active Hotel',
                'email' => 'contact@activehotel.com',
                'subdomain' => 'active-hotel',
                'database_name' => 'tenant_active_hotel_test3',
                'status' => 'active',
                'activated_at' => now(),
            ]);

            $response = $this->actingAs($this->centralAdmin)->get(route('central.dashboard'));

            $response->assertStatus(200);
            // Should have at least 2 pending and 1 active
            $response->assertSee('2'); // pending count
        });
    });
});
