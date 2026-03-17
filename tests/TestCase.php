<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Register Spatie Permission macro
        $this->registerPermissionMacro();
    }

    /**
     * Register permission macro for testing
     */
    protected function registerPermissionMacro(): void
    {
        Role::macro('givePermissionTo', function ($permissions) {
            $this->permissions()->sync($permissions);
            return $this;
        });
    }

    /**
     * Create a test user with specified role
     */
    protected function createUserWithRole(string $role, array $attributes = []): \App\Models\User
    {
        $user = \App\Models\User::factory()->create($attributes);
        $user->assignRole($role);
        return $user;
    }

    /**
     * Get API token for authenticated requests
     */
    protected function getApiToken(\App\Models\User $user): string
    {
        return $user->createToken('test-token')->plainTextToken;
    }

    /**
     * Seed default roles and permissions
     */
    protected function seedRolesAndPermissions(): void
    {
        $roles = ['super_admin', 'hotel_admin', 'front_desk', 'housekeeping', 'pos_cashier', 'hr_manager', 'accountant', 'maintenance'];
        
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
        
        // Common permissions
        $permissions = [
            'view reservations',
            'create reservations',
            'update reservations',
            'delete reservations',
            'check-in reservations',
            'check-out reservations',
            'view guests',
            'create guests',
            'update guests',
            'view rooms',
            'update rooms',
            'view reports',
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
