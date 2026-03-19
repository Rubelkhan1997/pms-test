<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'billing.currency', 'value' => 'USD', 'type' => 'string', 'group' => 'billing'],
            ['key' => 'billing.tax_rate', 'value' => '0', 'type' => 'decimal', 'group' => 'billing'],
            ['key' => 'billing.invoice_prefix', 'value' => 'INV', 'type' => 'string', 'group' => 'billing'],
            ['key' => 'email.from_address', 'value' => 'noreply@pms.test', 'type' => 'string', 'group' => 'email'],
            ['key' => 'email.from_name', 'value' => 'Hotel PMS', 'type' => 'string', 'group' => 'email'],
            ['key' => 'general.site_name', 'value' => 'Hotel PMS', 'type' => 'string', 'group' => 'general'],
            ['key' => 'general.timezone', 'value' => 'UTC', 'type' => 'string', 'group' => 'general'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}






