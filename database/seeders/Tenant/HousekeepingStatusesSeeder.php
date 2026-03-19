<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HousekeepingStatusesSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Clean', 'code' => 'clean', 'color' => '#22c55e'],
            ['name' => 'Dirty', 'code' => 'dirty', 'color' => '#ef4444'],
            ['name' => 'Inspected', 'code' => 'inspected', 'color' => '#3b82f6'],
            ['name' => 'Out of Order', 'code' => 'out_of_order', 'color' => '#f97316'],
            ['name' => 'Out of Service', 'code' => 'out_of_service', 'color' => '#a855f7'],
        ];

        foreach ($statuses as $status) {
            DB::table('housekeeping_statuses')->updateOrInsert(
                ['code' => $status['code']],
                array_merge($status, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}

