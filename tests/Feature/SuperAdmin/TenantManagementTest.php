<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
});

it('lists tenants paginated', function (): void {
    Tenant::factory()->count(5)->create();

    $this->withServerVariables(['HTTP_HOST' => config('app.admin_domain')])
         ->getJson('/api/v1/admin/tenants')
         ->assertOk()
         ->assertJsonPath('status', 1)
         ->assertJsonCount(5, 'data.items');
});

it('creates a tenant via API and provisions database', function (): void {
    $this->withServerVariables(['HTTP_HOST' => config('app.admin_domain')])
         ->postJson('/api/v1/admin/tenants', [
             'name'          => 'Ocean View Hotel',
             'domain'        => 'ocean.pms.test',
             'contact_name'  => 'John Doe',
             'contact_email' => 'john@ocean.com',
             'status'        => 'active',
         ])
         ->assertCreated()
         ->assertJsonPath('status', 1)
         ->assertJsonPath('data.name', 'Ocean View Hotel');

    expect(Tenant::on('landlord')->where('domain', 'ocean.pms.test')->exists())->toBeTrue();
});

it('suspends a tenant', function (): void {
    $tenant = Tenant::factory()->create(['status' => 'active']);

    $this->withServerVariables(['HTTP_HOST' => config('app.admin_domain')])
         ->patchJson("/api/v1/admin/tenants/{$tenant->id}/suspend")
         ->assertOk()
         ->assertJsonPath('data.status', 'suspended');

    expect($tenant->fresh()->status)->toBe('suspended');
});

it('activates a suspended tenant', function (): void {
    $tenant = Tenant::factory()->create(['status' => 'suspended']);

    $this->withServerVariables(['HTTP_HOST' => config('app.admin_domain')])
         ->patchJson("/api/v1/admin/tenants/{$tenant->id}/activate")
         ->assertOk()
         ->assertJsonPath('data.status', 'active');
});

it('rejects tenant creation with duplicate domain', function (): void {
    Tenant::factory()->create(['domain' => 'dup.pms.test']);

    $this->withServerVariables(['HTTP_HOST' => config('app.admin_domain')])
         ->postJson('/api/v1/admin/tenants', [
             'name'   => 'Another',
             'domain' => 'dup.pms.test',
         ])
         ->assertUnprocessable()
         ->assertJsonValidationErrors(['domain']);
});