<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'superadmin@pms.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        if (Schema::hasColumn('users', 'is_active')) {
            $user->update(['is_active' => true]);
        }
    }
}






