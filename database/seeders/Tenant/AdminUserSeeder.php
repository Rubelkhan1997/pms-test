<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = DB::table('hotels')->where('code', 'DEFAULT')->first();

        if (!$hotel) {
            return;
        }

        $user = User::firstOrCreate(
            ['email' => 'admin@tenant.local'],
            [
                'name' => 'Hotel Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        if (Schema::hasColumn('users', 'hotel_id')) {
            DB::table('users')->where('id', $user->id)->update([
                'hotel_id' => $hotel->id,
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasColumn('users', 'is_active')) {
            DB::table('users')->where('id', $user->id)->update([
                'is_active' => true,
                'updated_at' => now(),
            ]);
        }

        if (method_exists($user, 'assignRole')) {
            $user->assignRole('hotel_admin');
        }
    }
}

