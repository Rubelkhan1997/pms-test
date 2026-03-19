<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NightAuditDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $today = now()->toDateString();

        DB::table('business_dates')->updateOrInsert(
            ['date' => $today],
            [
                'is_open' => true,
                'is_current' => true,
                'opened_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('auto_audit_schedules')->updateOrInsert(
            ['scheduled_time' => '02:00:00'],
            [
                'is_enabled' => true,
                'days_of_week' => json_encode([0, 1, 2, 3, 4, 5, 6]),
                'auto_post_charges' => true,
                'auto_generate_reports' => true,
                'send_email_summary' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}

