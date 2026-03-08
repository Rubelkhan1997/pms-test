<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\HousekeepingStatus;
use App\Enums\POSOrderStatus;
use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Models\Hotel;
use App\Models\User;
use App\Modules\Booking\Models\OtaSync;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Models\Room;
use App\Modules\Guest\Models\GuestProfile;
use App\Modules\Housekeeping\Models\HousekeepingTask;
use App\Modules\Hr\Models\Employee;
use App\Modules\Mobile\Models\MobileTask;
use App\Modules\Pos\Models\PosOrder;
use App\Modules\Reports\Models\ReportSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $hotel = Hotel::query()->create([
            'name' => 'Demo Grand Hotel',
            'code' => 'DGH001',
            'timezone' => 'Asia/Dhaka',
            'currency' => 'USD',
            'email' => 'info@demogrand.test',
            'phone' => '+8801700000000',
            'address' => 'Dhaka, Bangladesh',
        ]);

        $superAdmin = User::query()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@pms.test',
            'password' => Hash::make('password'),
        ]);
        $superAdmin->assignRole('super_admin');

        $employeeUser = User::query()->create([
            'name' => 'Front Desk Agent',
            'email' => 'frontdesk@pms.test',
            'password' => Hash::make('password'),
        ]);
        $employeeUser->assignRole('front_desk');

        $room = Room::query()->create([
            'hotel_id' => $hotel->id,
            'number' => '101',
            'floor' => '1',
            'type' => 'Deluxe',
            'status' => RoomStatus::Available,
            'base_rate' => 120,
        ]);

        $guest = GuestProfile::query()->create([
            'hotel_id' => $hotel->id,
            'created_by' => $superAdmin->id,
            'reference' => 'GST-1001',
            'status' => 'active',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.test',
            'phone' => '+8801711111111',
        ]);

        Reservation::query()->create([
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'guest_profile_id' => $guest->id,
            'created_by' => $employeeUser->id,
            'reference' => 'RES-1001',
            'status' => ReservationStatus::Confirmed,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'adults' => 2,
            'children' => 0,
            'total_amount' => 150,
        ]);

        OtaSync::query()->create([
            'hotel_id' => $hotel->id,
            'created_by' => $superAdmin->id,
            'reference' => 'OTA-1001',
            'provider' => 'Booking.com',
            'status' => \App\Enums\PaymentStatus::Pending,
        ]);

        HousekeepingTask::query()->create([
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'created_by' => $superAdmin->id,
            'reference' => 'HK-1001',
            'status' => HousekeepingStatus::Pending,
            'scheduled_at' => now()->addHour(),
        ]);

        PosOrder::query()->create([
            'hotel_id' => $hotel->id,
            'created_by' => $superAdmin->id,
            'reference' => 'POS-1001',
            'outlet' => 'Restaurant',
            'status' => POSOrderStatus::Submitted,
            'scheduled_at' => now(),
        ]);

        ReportSnapshot::query()->create([
            'hotel_id' => $hotel->id,
            'created_by' => $superAdmin->id,
            'reference' => 'RPT-1001',
            'status' => 'ready',
            'report_type' => 'occupancy',
            'report_date' => now()->toDateString(),
        ]);

        MobileTask::query()->create([
            'hotel_id' => $hotel->id,
            'created_by' => $superAdmin->id,
            'reference' => 'MBL-1001',
            'status' => 'pending',
            'scheduled_at' => now()->addMinutes(30),
        ]);

        Employee::query()->create([
            'hotel_id' => $hotel->id,
            'user_id' => $employeeUser->id,
            'created_by' => $superAdmin->id,
            'reference' => 'EMP-1001',
            'status' => 'active',
            'department' => 'Front Desk',
            'scheduled_at' => now(),
        ]);
    }
}
