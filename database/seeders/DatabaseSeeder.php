<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\HousekeepingStatus;
use App\Enums\POSOrderStatus;
use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Models\User;
use App\Modules\Booking\Models\OtaSync;
use App\Modules\FrontDesk\Models\Hotel;
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
        $this->command->info('🌱 Starting database seeding...');

        // Seed roles and permissions first
        $this->call(RolePermissionSeeder::class);

        // Create or get hotel
        $hotel = Hotel::query()->firstOrCreate(
            ['code' => 'DGH001'],
            [
                'name' => 'Demo Grand Hotel',
                'timezone' => 'Asia/Dhaka',
                'currency' => 'BDT',
                'email' => 'info@demogrand.test',
                'phone' => '+8801700000000',
                'address' => 'Dhaka, Bangladesh',
            ]
        );
        $this->command->info('✅ Hotel: ' . $hotel->name);

        // Create users
        $superAdmin = User::query()->firstOrCreate(
            ['email' => 'superadmin@pms.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        $superAdmin->assignRole('super_admin');
        $this->command->info('✅ Super admin user');

        $employeeUser = User::query()->firstOrCreate(
            ['email' => 'frontdesk@pms.test'],
            [
                'name' => 'Front Desk Agent',
                'password' => Hash::make('password'),
            ]
        );
        $employeeUser->assignRole('front_desk');
        $this->command->info('✅ Front desk user');

        // Seed rooms (20 rooms)
        $this->seedRooms($hotel);

        // Seed guests (20 guests)
        $this->seedGuests($hotel, $superAdmin);

        // Seed sample reservations
        $this->seedReservations($hotel, $superAdmin, $employeeUser);


        $this->command->info('🎉 Database seeding completed!');
        $this->command->info('');
        $this->command->info('📊 Login Credentials:');
        $this->command->info('   Super Admin: superadmin@pms.test / password');
        $this->command->info('   Front Desk:  frontdesk@pms.test / password');
    }

    /**
     * Seed rooms for the hotel.
     */
    private function seedRooms(Hotel $hotel): void
    {
        // Check if rooms already exist
        if (Room::where('hotel_id', $hotel->id)->exists()) {
            $this->command->info('⏭️  Rooms already exist, skipping...');
            return;
        }

        $rooms = [
            // Floor 1 - Standard Rooms
            ['number' => '101', 'floor' => '1', 'type' => 'Standard', 'base_rate' => 80],
            ['number' => '102', 'floor' => '1', 'type' => 'Standard', 'base_rate' => 80],
            ['number' => '103', 'floor' => '1', 'type' => 'Standard', 'base_rate' => 80],
            ['number' => '104', 'floor' => '1', 'type' => 'Standard', 'base_rate' => 80],
            
            // Floor 1 - Deluxe Rooms
            ['number' => '105', 'floor' => '1', 'type' => 'Deluxe', 'base_rate' => 120],
            ['number' => '106', 'floor' => '1', 'type' => 'Deluxe', 'base_rate' => 120],
            ['number' => '107', 'floor' => '1', 'type' => 'Deluxe', 'base_rate' => 120],
            ['number' => '108', 'floor' => '1', 'type' => 'Deluxe', 'base_rate' => 120],
            
            // Floor 2 - Standard Rooms
            ['number' => '201', 'floor' => '2', 'type' => 'Standard', 'base_rate' => 85],
            ['number' => '202', 'floor' => '2', 'type' => 'Standard', 'base_rate' => 85],
            ['number' => '203', 'floor' => '2', 'type' => 'Standard', 'base_rate' => 85],
            ['number' => '204', 'floor' => '2', 'type' => 'Standard', 'base_rate' => 85],
            
            // Floor 2 - Deluxe Rooms
            ['number' => '205', 'floor' => '2', 'type' => 'Deluxe', 'base_rate' => 125],
            ['number' => '206', 'floor' => '2', 'type' => 'Deluxe', 'base_rate' => 125],
            ['number' => '207', 'floor' => '2', 'type' => 'Deluxe', 'base_rate' => 125],
            ['number' => '208', 'floor' => '2', 'type' => 'Deluxe', 'base_rate' => 125],
            
            // Floor 3 - Suite Rooms
            ['number' => '301', 'floor' => '3', 'type' => 'Suite', 'base_rate' => 200],
            ['number' => '302', 'floor' => '3', 'type' => 'Suite', 'base_rate' => 200],
            ['number' => '303', 'floor' => '3', 'type' => 'Suite', 'base_rate' => 250],
            ['number' => '304', 'floor' => '3', 'type' => 'Suite', 'base_rate' => 250],
            
            // Floor 3 - Executive Suite
            ['number' => '305', 'floor' => '3', 'type' => 'Executive Suite', 'base_rate' => 350],
            ['number' => '306', 'floor' => '3', 'type' => 'Executive Suite', 'base_rate' => 350],
        ];

        foreach ($rooms as $roomData) {
            Room::query()->create([
                'hotel_id' => $hotel->id,
                'number' => $roomData['number'],
                'floor' => $roomData['floor'],
                'type' => $roomData['type'],
                'status' => RoomStatus::Available,
                'base_rate' => $roomData['base_rate'],
            ]);
        }

        $this->command->info('✅ Created ' . count($rooms) . ' rooms');
    }

    /**
     * Seed guest profiles.
     */
    private function seedGuests(Hotel $hotel, User $createdBy): void
    {
        // Check if guests already exist
        if (GuestProfile::where('hotel_id', $hotel->id)->exists()) {
            $this->command->info('⏭️  Guests already exist, skipping...');
            return;
        }

        $guests = [
            ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@example.test', 'phone' => '+8801711111111'],
            ['first_name' => 'Jane', 'last_name' => 'Smith', 'email' => 'jane@example.test', 'phone' => '+8801722222222'],
            ['first_name' => 'Michael', 'last_name' => 'Johnson', 'email' => 'michael@example.test', 'phone' => '+8801733333333'],
            ['first_name' => 'Emily', 'last_name' => 'Brown', 'email' => 'emily@example.test', 'phone' => '+8801744444444'],
            ['first_name' => 'David', 'last_name' => 'Wilson', 'email' => 'david@example.test', 'phone' => '+8801755555555'],
            ['first_name' => 'Sarah', 'last_name' => 'Taylor', 'email' => 'sarah@example.test', 'phone' => '+8801766666666'],
            ['first_name' => 'James', 'last_name' => 'Anderson', 'email' => 'james@example.test', 'phone' => '+8801777777777'],
            ['first_name' => 'Emma', 'last_name' => 'Thomas', 'email' => 'emma@example.test', 'phone' => '+8801788888888'],
            ['first_name' => 'Robert', 'last_name' => 'Jackson', 'email' => 'robert@example.test', 'phone' => '+8801799999999'],
            ['first_name' => 'Olivia', 'last_name' => 'White', 'email' => 'olivia@example.test', 'phone' => '+8801700000001'],
            ['first_name' => 'William', 'last_name' => 'Harris', 'email' => 'william@example.test', 'phone' => '+8801700000002'],
            ['first_name' => 'Sophia', 'last_name' => 'Martin', 'email' => 'sophia@example.test', 'phone' => '+8801700000003'],
            ['first_name' => 'Daniel', 'last_name' => 'Garcia', 'email' => 'daniel@example.test', 'phone' => '+8801700000004'],
            ['first_name' => 'Isabella', 'last_name' => 'Martinez', 'email' => 'isabella@example.test', 'phone' => '+8801700000005'],
            ['first_name' => 'Matthew', 'last_name' => 'Robinson', 'email' => 'matthew@example.test', 'phone' => '+8801700000006'],
            ['first_name' => 'Mia', 'last_name' => 'Clark', 'email' => 'mia@example.test', 'phone' => '+8801700000007'],
            ['first_name' => 'Andrew', 'last_name' => 'Rodriguez', 'email' => 'andrew@example.test', 'phone' => '+8801700000008'],
            ['first_name' => 'Charlotte', 'last_name' => 'Lewis', 'email' => 'charlotte@example.test', 'phone' => '+8801700000009'],
            ['first_name' => 'Joseph', 'last_name' => 'Lee', 'email' => 'joseph@example.test', 'phone' => '+8801700000010'],
            ['first_name' => 'Amelia', 'last_name' => 'Walker', 'email' => 'amelia@example.test', 'phone' => '+8801700000011'],
        ];

        $referenceCounter = 1001;
        foreach ($guests as $guestData) {
            GuestProfile::query()->create([
                'hotel_id' => $hotel->id,
                'created_by' => $createdBy->id,
                'reference' => 'GST-' . $referenceCounter++,
                'status' => 'active',
                'first_name' => $guestData['first_name'],
                'last_name' => $guestData['last_name'],
                'email' => $guestData['email'],
                'phone' => $guestData['phone'],
            ]);
        }

        $this->command->info('✅ Created ' . count($guests) . ' guest profiles');
    }

    /**
     * Seed sample reservations.
     */
    private function seedReservations(Hotel $hotel, User $createdBy, User $employee): void
    {
        // Check if reservations already exist
        if (Reservation::where('hotel_id', $hotel->id)->exists()) {
            $this->command->info('⏭️  Reservations already exist, skipping...');
            return;
        }

        $rooms = Room::where('hotel_id', $hotel->id)->limit(10)->get();
        $guests = GuestProfile::where('hotel_id', $hotel->id)->limit(10)->get();

        if ($rooms->isEmpty() || $guests->isEmpty()) {
            return;
        }

        $reservations = [
            ['status' => ReservationStatus::Confirmed, 'days_offset' => 0, 'nights' => 2],
            ['status' => ReservationStatus::CheckedIn, 'days_offset' => -1, 'nights' => 3],
            ['status' => ReservationStatus::Draft, 'days_offset' => 5, 'nights' => 2],
            ['status' => ReservationStatus::Confirmed, 'days_offset' => 10, 'nights' => 4],
            ['status' => ReservationStatus::CheckedOut, 'days_offset' => -5, 'nights' => 2],
            ['status' => ReservationStatus::Cancelled, 'days_offset' => 3, 'nights' => 1],
            ['status' => ReservationStatus::Confirmed, 'days_offset' => 7, 'nights' => 3],
            ['status' => ReservationStatus::Draft, 'days_offset' => 15, 'nights' => 5],
        ];

        $referenceCounter = 1001;
        foreach ($reservations as $index => $resData) {
            $room = $rooms->get($index % $rooms->count());
            $guest = $guests->get($index % $guests->count());

            $checkIn = now()->addDays($resData['days_offset']);
            $checkOut = $checkIn->copy()->addDays($resData['nights']);

            Reservation::query()->create([
                'hotel_id' => $hotel->id,
                'room_id' => $room->id,
                'guest_id' => $guest->id,
                'created_by' => $employee->id,
                'reference' => 'RES-' . $referenceCounter++,
                'status' => $resData['status'],
                'check_in_date' => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'adults' => rand(1, 4),
                'children' => rand(0, 2),
                'total_amount' => $room->base_rate * $resData['nights'],
            ]);
        }

        $this->command->info('✅ Created ' . count($reservations) . ' reservations');
    }


}
