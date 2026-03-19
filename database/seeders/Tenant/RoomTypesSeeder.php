<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoomTypesSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = DB::table('hotels')->where('code', 'DEFAULT')->first();

        if (!$hotel) {
            return;
        }

        $roomTypes = [
            [
                'name' => 'Standard Room',
                'code' => 'STD',
                'base_occupancy' => 2,
                'max_occupancy' => 3,
                'max_adults' => 2,
                'max_children' => 1,
                'bed_type' => 'Queen',
                'bed_count' => 1,
                'room_size' => 25.00,
                'size_unit' => 'sqm',
                'view_type' => 'City',
                'base_price' => 100.00,
                'is_refundable' => true,
                'is_smoking_allowed' => false,
                'description' => 'Comfortable standard room with city view',
                'is_active' => true,
            ],
            [
                'name' => 'Deluxe Room',
                'code' => 'DLX',
                'base_occupancy' => 2,
                'max_occupancy' => 4,
                'max_adults' => 2,
                'max_children' => 2,
                'bed_type' => 'King',
                'bed_count' => 1,
                'room_size' => 35.00,
                'size_unit' => 'sqm',
                'view_type' => 'Ocean',
                'base_price' => 150.00,
                'is_refundable' => true,
                'is_smoking_allowed' => false,
                'description' => 'Spacious deluxe room with enhanced amenities',
                'is_active' => true,
            ],
            [
                'name' => 'Executive Suite',
                'code' => 'STE',
                'base_occupancy' => 2,
                'max_occupancy' => 5,
                'max_adults' => 3,
                'max_children' => 2,
                'bed_type' => 'King',
                'bed_count' => 1,
                'room_size' => 55.00,
                'size_unit' => 'sqm',
                'view_type' => 'Ocean',
                'base_price' => 250.00,
                'is_refundable' => true,
                'is_smoking_allowed' => false,
                'description' => 'Luxurious suite with separate living area',
                'is_active' => true,
            ],
        ];

        foreach ($roomTypes as $roomType) {
            $slug = Str::slug($roomType['name'] . '-' . $hotel->code);

            DB::table('room_types')->updateOrInsert(
                ['slug' => $slug],
                array_merge($roomType, [
                    'hotel_id' => $hotel->id,
                    'slug' => $slug,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}

