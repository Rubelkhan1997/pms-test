<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $resources = [
            'reservations',
            'ota_syncs',
            'guest_profiles',
            'housekeeping_tasks',
            'pos_orders',
            'report_snapshots',
            'mobile_tasks',
            'employees',
            'maintenance_requests',
        ];

        $permissions = [];

        foreach ($resources as $resource) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                $permissions[] = "$action $resource";
            }
        }

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $roleMap = [
            'super_admin' => $permissions,
            'hotel_admin' => $permissions,
            'front_desk' => ['view reservations', 'create reservations', 'edit reservations', 'view guest_profiles'],
            'housekeeping' => ['view housekeeping_tasks', 'create housekeeping_tasks', 'edit housekeeping_tasks', 'view maintenance_requests'],
            'pos_cashier' => ['view pos_orders', 'create pos_orders', 'edit pos_orders'],
            'hr_manager' => ['view employees', 'create employees', 'edit employees'],
            'accountant' => ['view report_snapshots', 'view ota_syncs', 'view pos_orders'],
            'maintenance' => ['view maintenance_requests', 'create maintenance_requests', 'edit maintenance_requests'],
        ];

        foreach ($roleMap as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($rolePermissions);
        }
    }
}
