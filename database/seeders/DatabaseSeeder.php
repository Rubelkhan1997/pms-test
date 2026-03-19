<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Central\SubscriptionPlansSeeder;
use Database\Seeders\Central\SystemSettingsSeeder;
use Database\Seeders\Central\SuperAdminSeeder;

/**
 * Central Database Seeder
 *
 * Seeds the CENTRAL database with subscription plans, system settings,
 * and the super admin user.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the central database.
     */
    public function run(): void
    {
        $this->call([
            SubscriptionPlansSeeder::class,
            SystemSettingsSeeder::class,
            SuperAdminSeeder::class,
        ]);
    }
}
