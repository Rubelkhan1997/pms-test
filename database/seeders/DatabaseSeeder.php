<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Central Database Seeder
 * 
 * Seeds the CENTRAL database with subscription plans and super admin.
 * This runs once on the central database that manages all tenants.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the central database.
     */
    public function run(): void
    {
        // Create subscription plans
        $this->createSubscriptionPlans();
        
        // Create super admin user
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@pms.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        // Create system settings
        $this->seedSystemSettings();
    }
    
    /**
     * Create subscription plans.
     */
    protected function createSubscriptionPlans(): void
    {
        // Starter Plan
        SubscriptionPlan::create([
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
        ]);
        
        // Professional Plan
        SubscriptionPlan::create([
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
        ]);
        
        // Enterprise Plan
        SubscriptionPlan::create([
            'name' => 'Enterprise',
            'code' => 'enterprise',
            'description' => 'For large hotels and chains',
            'price_monthly' => 249.00,
            'price_yearly' => 2490.00,
            'trial_days' => 30,
            'max_properties' => -1, // Unlimited
            'max_rooms' => -1, // Unlimited
            'max_users' => -1, // Unlimited
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
        ]);
    }
    
    /**
     * Seed system settings.
     */
    protected function seedSystemSettings(): void
    {
        $settings = [
            // Billing settings
            ['key' => 'billing.currency', 'value' => 'USD', 'type' => 'string', 'group' => 'billing'],
            ['key' => 'billing.tax_rate', 'value' => '0', 'type' => 'decimal', 'group' => 'billing'],
            ['key' => 'billing.invoice_prefix', 'value' => 'INV', 'type' => 'string', 'group' => 'billing'],
            
            // Email settings
            ['key' => 'email.from_address', 'value' => 'noreply@pms.test', 'type' => 'string', 'group' => 'email'],
            ['key' => 'email.from_name', 'value' => 'Hotel PMS', 'type' => 'string', 'group' => 'email'],
            
            // General settings
            ['key' => 'general.site_name', 'value' => 'Hotel PMS', 'type' => 'string', 'group' => 'general'],
            ['key' => 'general.timezone', 'value' => 'UTC', 'type' => 'string', 'group' => 'general'],
        ];
        
        foreach ($settings as $setting) {
            \App\Models\SystemSetting::create($setting);
        }
    }
}
