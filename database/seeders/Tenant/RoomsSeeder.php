<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomsSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = DB::table('hotels')->where('code', 'DEFAULT')->first();

        if (!$hotel) {
            return;
        }

        $standard = DB::table('room_types')->where('hotel_id', $hotel->id)->where('code', 'STD')->first();
        $deluxe = DB::table('room_types')->where('hotel_id', $hotel->id)->where('code', 'DLX')->first();

        if ($standard) {
            for ($i = 101; $i <= 110; $i++) {
                DB::table('rooms')->updateOrInsert(
                    [
                        'hotel_id' => $hotel->id,
                        'room_number' => (string) $i,
                        'building_id' => null,
                    ],
                    [
                        'room_type_id' => $standard->id,
                        'room_code' => 'STD-' . $i,
                        'status' => 'available',
                        'is_active' => true,
                        'is_visible' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        if ($deluxe) {
            for ($i = 201; $i <= 205; $i++) {
                DB::table('rooms')->updateOrInsert(
                    [
                        'hotel_id' => $hotel->id,
                        'room_number' => (string) $i,
                        'building_id' => null,
                    ],
                    [
                        'room_type_id' => $deluxe->id,
                        'room_code' => 'DLX-' . $i,
                        'status' => 'available',
                        'is_active' => true,
                        'is_visible' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}

