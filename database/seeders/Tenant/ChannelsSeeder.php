<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChannelsSeeder extends Seeder
{
    public function run(): void
    {
        $channels = [
            ['name' => 'Booking.com', 'code' => 'booking_com', 'api_type' => 'xml'],
            ['name' => 'Expedia', 'code' => 'expedia', 'api_type' => 'json'],
            ['name' => 'Agoda', 'code' => 'agoda', 'api_type' => 'xml'],
            ['name' => 'Airbnb', 'code' => 'airbnb', 'api_type' => 'rest'],
        ];

        foreach ($channels as $channel) {
            DB::table('channels')->updateOrInsert(
                ['code' => $channel['code']],
                array_merge($channel, [
                    'is_active' => true,
                    'is_test_mode' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}

