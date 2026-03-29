<?php

namespace Tests\Feature\Central;

use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Tenant Lifecycle Tests
 *
 * Tests for tenant suspension, reactivation, and lifecycle management.
 */
describe('Tenant Lifecycle Management', function () {
    beforeEach(function () {
        // Create roles
        $this->superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $this->tenantOwnerRole = Role::firstOrCreate(['name' => 'tenant_owner']);

        // Create a central admin user
        $this->centralAdmin = User::factory()->create([
            'email' => 'admin@central.com',
        ]);
        $this->centralAdmin->assignRole($this->superAdminRole);

        // Create active tenant
        $this->activeTenant = Tenant::create([
            'name' => 'Active Hotel',
            'email' => 'contact@activehotel.com',
            'subdomain' => 'active-hotel',
            'database_name' => 'tenant_active_hotel_lifecycle',
            'status' => 'active',
            'activated_at' => now(),
        ]);

        // Create suspended tenant
        $this->suspendedTenant = Tenant::create([
            'name' => 'Suspended Hotel',
            'email' => 'contact@suspendedhotel.com',
            'subdomain' => 'suspended-hotel',
            'database_name' => 'tenant_suspended_hotel_lifecycle',
            'status' => 'suspended',
            'suspended_at' => now(),
        ]);
    });

    describe('Suspend Tenant', function () {
        it('requires authentication', function () {
            $response = $this->post(route('central.tenants.suspend', $this->activeTenant));

            $response->assertRedirect(route('login'));
        });

        it('requires super_admin role', function () {
            $regularUser = User::factory()->create();

            $response = $this->actingAs($regularUser)->post(route('central.tenants.suspend', $this->activeTenant));

            $response->assertStatus(403);
        });

        it('suspends active tenant successfully', function () {
            $response = $this->actingAs($this->centralAdmin)->post(route('central.tenants.suspend', $this->activeTenant));

            $response->assertRedirect();
            $response->assertSessionHas('success', 'Tenant suspended successfully.');

            $this->assertDatabaseHas('tenants', [
                'id' => $this->activeTenant->id,
                'status' => 'suspended',
            ]);
        });

        it('sets suspended_at timestamp', function () {
            $this->actingAs($this->centralAdmin)->post(route('central.tenants.suspend', $this->activeTenant));

            $tenant = Tenant::find($this->activeTenant->id);

            expect($tenant->suspended_at)->not->toBeNull();
            expect($tenant->suspended_at)->toBePast();
        });

        it('cannot suspend already suspended tenant', function () {
            $response = $this->actingAs($this->centralAdmin)->post(route('central.tenants.suspend', $this->suspendedTenant));

            $response->assertRedirect();
            $response->assertSessionHas('error', 'Tenant is not active.');
        });

        it('cannot suspend pending tenant', function () {
            $pendingTenant = Tenant::create([
                'name' => 'Pending Hotel',
                'email' => 'contact@pendinghotel.com',
                'subdomain' => 'pending-hotel',
                'database_name' => 'tenant_pending_hotel_lifecycle',
                'status' => 'pending',
                'trial_ends_at' => now()->addDays(14),
            ]);

            $response = $this->actingAs($this->centralAdmin)->post(route('central.tenants.suspend', $pendingTenant));

            $response->assertRedirect();
            $response->assertSessionHas('error', 'Tenant is not active.');
        });
    });

    describe('Reactivate Tenant', function () {
        it('requires authentication', function () {
            $response = $this->post(route('central.tenants.reactivate', $this->suspendedTenant));

            $response->assertRedirect(route('login'));
        });

        it('requires super_admin role', function () {
            $regularUser = User::factory()->create();

            $response = $this->actingAs($regularUser)->post(route('central.tenants.reactivate', $this->suspendedTenant));

            $response->assertStatus(403);
        });

        it('reactivates suspended tenant successfully', function () {
            $response = $this->actingAs($this->centralAdmin)->post(route('central.tenants.reactivate', $this->suspendedTenant));

            $response->assertRedirect();
            $response->assertSessionHas('success', 'Tenant reactivated successfully.');

            $this->assertDatabaseHas('tenants', [
                'id' => $this->suspendedTenant->id,
                'status' => 'active',
            ]);
        });

        it('clears suspended_at timestamp on reactivation', function () {
            $this->actingAs($this->centralAdmin)->post(route('central.tenants.reactivate', $this->suspendedTenant));

            $tenant = Tenant::find($this->suspendedTenant->id);

            expect($tenant->suspended_at)->toBeNull();
        });

        it('cannot reactivate active tenant', function () {
            $response = $this->actingAs($this->centralAdmin)->post(route('central.tenants.reactivate', $this->activeTenant));

            $response->assertRedirect();
            $response->assertSessionHas('error', 'Tenant is not suspended.');
        });

        it('cannot reactivate pending tenant', function () {
            $pendingTenant = Tenant::create([
                'name' => 'Pending Hotel',
                'email' => 'contact@pendinghotel.com',
                'subdomain' => 'pending-hotel',
                'database_name' => 'tenant_pending_hotel_lifecycle2',
                'status' => 'pending',
                'trial_ends_at' => now()->addDays(14),
            ]);

            $response = $this->actingAs($this->centralAdmin)->post(route('central.tenants.reactivate', $pendingTenant));

            $response->assertRedirect();
            $response->assertSessionHas('error', 'Tenant is not suspended.');
        });
    });

    describe('Tenant Status Transitions', function () {
        it('allows full lifecycle: pending -> active -> suspended -> active', function () {
            $tenant = Tenant::create([
                'name' => 'Lifecycle Test Hotel',
                'email' => 'contact@lifecyclehotel.com',
                'subdomain' => 'lifecycle-hotel',
                'database_name' => 'tenant_lifecycle_hotel_test',
                'status' => 'pending',
                'trial_ends_at' => now()->addDays(14),
            ]);

            expect($tenant->status)->toBe('pending');

            // Activate
            $tenant->activate();
            expect($tenant->fresh()->status)->toBe('active');
            expect($tenant->fresh()->activated_at)->not->toBeNull();

            // Suspend
            $tenant->suspend();
            expect($tenant->fresh()->status)->toBe('suspended');
            expect($tenant->fresh()->suspended_at)->not->toBeNull();

            // Reactivate
            $tenant->reactivate();
            expect($tenant->fresh()->status)->toBe('active');
            expect($tenant->fresh()->suspended_at)->toBeNull();
        });

        it('allows cancellation', function () {
            $tenant = Tenant::create([
                'name' => 'Cancel Test Hotel',
                'email' => 'contact@cancelhotel.com',
                'subdomain' => 'cancel-hotel',
                'database_name' => 'tenant_cancel_hotel_test',
                'status' => 'active',
                'activated_at' => now(),
            ]);

            $tenant->cancel('Customer requested cancellation');

            expect($tenant->fresh()->status)->toBe('cancelled');
            expect($tenant->fresh()->cancelled_at)->not->toBeNull();
            expect($tenant->fresh()->metadata['cancellation_reason'])->toBe('Customer requested cancellation');
        });
    });

    describe('Tenant Model Methods', function () {
        it('isActive returns correct status', function () {
            $activeTenant = Tenant::create([
                'name' => 'Active Test',
                'email' => 'active@test.com',
                'subdomain' => 'active-test',
                'database_name' => 'tenant_active_test',
                'status' => 'active',
            ]);

            $suspendedTenant = Tenant::create([
                'name' => 'Suspended Test',
                'email' => 'suspended@test.com',
                'subdomain' => 'suspended-test',
                'database_name' => 'tenant_suspended_test',
                'status' => 'suspended',
            ]);

            expect($activeTenant->isActive())->toBeTrue();
            expect($suspendedTenant->isActive())->toBeFalse();
        });

        it('isSuspended returns correct status', function () {
            $activeTenant = Tenant::create([
                'name' => 'Active Test 2',
                'email' => 'active2@test.com',
                'subdomain' => 'active-test2',
                'database_name' => 'tenant_active_test2',
                'status' => 'active',
            ]);

            $suspendedTenant = Tenant::create([
                'name' => 'Suspended Test 2',
                'email' => 'suspended2@test.com',
                'subdomain' => 'suspended-test2',
                'database_name' => 'tenant_suspended_test2',
                'status' => 'suspended',
            ]);

            expect($activeTenant->isSuspended())->toBeFalse();
            expect($suspendedTenant->isSuspended())->toBeTrue();
        });

        it('isOnTrial returns true for tenants with future trial_ends_at', function () {
            $trialTenant = Tenant::create([
                'name' => 'Trial Test',
                'email' => 'trial@test.com',
                'subdomain' => 'trial-test',
                'database_name' => 'tenant_trial_test',
                'status' => 'active',
                'trial_ends_at' => now()->addDays(10),
            ]);

            $expiredTenant = Tenant::create([
                'name' => 'Expired Test',
                'email' => 'expired@test.com',
                'subdomain' => 'expired-test',
                'database_name' => 'tenant_expired_test',
                'status' => 'active',
                'trial_ends_at' => now()->subDays(5),
            ]);

            expect($trialTenant->isOnTrial())->toBeTrue();
            expect($expiredTenant->isOnTrial())->toBeFalse();
        });
    });

    describe('Reset Admin Password', function () {
        it('requires authentication', function () {
            $response = $this->post(route('central.tenants.reset-admin-password', $this->activeTenant));

            $response->assertRedirect(route('login'));
        });

        it('requires super_admin role', function () {
            $regularUser = User::factory()->create();

            $response = $this->actingAs($regularUser)->post(route('central.tenants.reset-admin-password', $this->activeTenant));

            $response->assertStatus(403);
        });

        it('returns error if no admin email found', function () {
            $tenantWithoutOwner = Tenant::create([
                'name' => 'No Owner Hotel',
                'email' => '',
                'subdomain' => 'no-owner-hotel',
                'database_name' => 'tenant_no_owner_hotel',
                'status' => 'active',
                'activated_at' => now(),
            ]);

            $response = $this->actingAs($this->centralAdmin)->post(route('central.tenants.reset-admin-password', $tenantWithoutOwner));

            $response->assertRedirect();
            $response->assertSessionHas('error', 'No admin email found for this tenant.');
        });
    });
});
