<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Tenant\AdminUserSeeder;
use Database\Seeders\Tenant\ChannelsSeeder;
use Database\Seeders\Tenant\HotelSeeder;
use Database\Seeders\Tenant\HousekeepingStatusesSeeder;
use Database\Seeders\Tenant\HrDefaultsSeeder;
use Database\Seeders\Tenant\NightAuditDefaultsSeeder;
use Database\Seeders\Tenant\PosDefaultsSeeder;
use Database\Seeders\Tenant\RatePlansSeeder;
use Database\Seeders\Tenant\RoomsSeeder;
use Database\Seeders\Tenant\RoomTypesSeeder;

/**
 * Tenant Database Seeder
 *
 * Seeds initial data for each tenant database.
 */
class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the tenant database.
     */
    public function run(): void
    {
        $this->call([
            HotelSeeder::class,
            RoomTypesSeeder::class,
            RoomsSeeder::class,
            RatePlansSeeder::class,
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            HousekeepingStatusesSeeder::class,
            ChannelsSeeder::class,
            PosDefaultsSeeder::class,
            NightAuditDefaultsSeeder::class,
            HrDefaultsSeeder::class,
        ]);
    }
}
