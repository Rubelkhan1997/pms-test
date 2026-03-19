<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'code' => 'starter',
                'description' => 'Perfect for small hotels and B&Bs',
                'price_monthly' => 49.00,
                'price_yearly' => 490.00,
                'trial_days' => 14,
                'max_properties' => 1,
                'max_rooms' => 20,
                'max_users' => 5,
                'features' => [
                    'reservations',
                    'guest_management',
                    'basic_reports',
                    'email_support',
                ],
                'limits' => [
                    'properties' => 1,
                    'rooms' => 20,
                    'users' => 5,
                    'api_calls_per_month' => 10000,
                    'storage_gb' => 5,
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Professional',
                'code' => 'professional',
                'description' => 'For growing hotels and small chains',
                'price_monthly' => 99.00,
                'price_yearly' => 990.00,
                'trial_days' => 14,
                'max_properties' => 5,
                'max_rooms' => 100,
                'max_users' => 20,
                'features' => [
                    'reservations',
                    'guest_management',
                    'room_management',
                    'accounting',
                    'advanced_reports',
                    'ota_integration',
                    'email_support',
                    'phone_support',
                ],
                'limits' => [
                    'properties' => 5,
                    'rooms' => 100,
                    'users' => 20,
                    'api_calls_per_month' => 50000,
                    'storage_gb' => 25,
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'code' => 'enterprise',
                'description' => 'For large hotels and chains',
                'price_monthly' => 249.00,
                'price_yearly' => 2490.00,
                'trial_days' => 30,
                'max_properties' => -1,
                'max_rooms' => -1,
                'max_users' => -1,
                'features' => [
                    'reservations',
                    'guest_management',
                    'room_management',
                    'accounting',
                    'advanced_reports',
                    'ota_integration',
                    'channel_manager',
                    'pos_system',
                    'housekeeping',
                    'hr_management',
                    'api_access',
                    'custom_integrations',
                    'priority_support',
                    'dedicated_account_manager',
                ],
                'limits' => [
                    'properties' => -1,
                    'rooms' => -1,
                    'users' => -1,
                    'api_calls_per_month' => -1,
                    'storage_gb' => 100,
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['code' => $plan['code']],
                $plan
            );
        }
    }
}






