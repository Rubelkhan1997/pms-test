<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RatePlansSeeder extends Seeder
{
    public function run(): void
    {
        $roomTypes = DB::table('room_types')->get()->keyBy('code');

        if ($roomTypes->isEmpty()) {
            return;
        }

        $ratePlans = [
            [
                'name' => 'Best Available Rate',
                'code' => 'BAR',
                'is_refundable' => true,
                'cancellation_policy' => 'flexible',
                'free_cancellation_hours' => 24,
                'meal_plan' => 'room_only',
                'min_stay' => 1,
                'max_stay' => 30,
                'advance_booking_days' => 0,
                'max_advance_booking_days' => 365,
                'is_active' => true,
                'is_public' => true,
            ],
            [
                'name' => 'Non-Refundable',
                'code' => 'NONREF',
                'is_refundable' => false,
                'cancellation_policy' => 'non_refundable',
                'free_cancellation_hours' => 0,
                'meal_plan' => 'room_only',
                'min_stay' => 1,
                'max_stay' => 14,
                'advance_booking_days' => 7,
                'max_advance_booking_days' => 90,
                'is_active' => true,
                'is_public' => true,
            ],
        ];

        foreach ($ratePlans as $plan) {
            DB::table('rate_plans')->updateOrInsert(
                ['code' => $plan['code']],
                array_merge($plan, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $bar = DB::table('rate_plans')->where('code', 'BAR')->first();
        $nonref = DB::table('rate_plans')->where('code', 'NONREF')->first();

        $links = [
            ['plan' => $bar, 'room_code' => 'STD', 'price' => 100.00],
            ['plan' => $bar, 'room_code' => 'DLX', 'price' => 150.00],
            ['plan' => $nonref, 'room_code' => 'STD', 'price' => 85.00],
        ];

        foreach ($links as $link) {
            $plan = $link['plan'];
            $room = $roomTypes->get($link['room_code']);

            if (!$plan || !$room) {
                continue;
            }

            DB::table('rate_plan_room_types')->updateOrInsert(
                [
                    'rate_plan_id' => $plan->id,
                    'room_type_id' => $room->id,
                ],
                [
                    'base_price' => $link['price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

