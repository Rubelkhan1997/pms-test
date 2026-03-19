<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PosDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $outlets = [
            ['name' => 'Main Restaurant', 'code' => 'restaurant', 'type' => 'restaurant'],
            ['name' => 'Lobby Bar', 'code' => 'bar', 'type' => 'bar'],
            ['name' => 'Spa & Wellness', 'code' => 'spa', 'type' => 'spa'],
            ['name' => 'Minibar', 'code' => 'minibar', 'type' => 'minibar'],
        ];

        foreach ($outlets as $outlet) {
            DB::table('outlets')->updateOrInsert(
                ['code' => $outlet['code']],
                array_merge($outlet, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $methods = [
            ['name' => 'Cash', 'code' => 'cash', 'type' => 'cash', 'allows_room_charge' => false],
            ['name' => 'Visa', 'code' => 'visa', 'type' => 'card', 'allows_room_charge' => false],
            ['name' => 'MasterCard', 'code' => 'mastercard', 'type' => 'card', 'allows_room_charge' => false],
            ['name' => 'Room Charge', 'code' => 'room_charge', 'type' => 'room_charge', 'allows_room_charge' => true],
        ];

        foreach ($methods as $method) {
            DB::table('pos_payment_methods')->updateOrInsert(
                ['code' => $method['code']],
                array_merge($method, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}

