<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        $existing = DB::table('hotels')->where('code', 'DEFAULT')->first();

        if ($existing) {
            return;
        }

        DB::table('hotels')->insert([
            'uuid' => (string) Str::uuid(),
            'name' => 'Default Property',
            'code' => 'DEFAULT',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'check_in_time' => '14:00:00',
            'check_out_time' => '12:00:00',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

