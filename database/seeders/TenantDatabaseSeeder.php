<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\User;
use App\Modules\FrontDesk\Models\Room;
use App\Modules\FrontDesk\Models\RoomType;
use App\Modules\FrontDesk\Models\RatePlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Tenant Database Seeder
 * 
 * Seeds initial data for each tenant database.
 * This runs on each new tenant's isolated database.
 */
class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the tenant database.
     */
    public function run(): void
    {
        // Create default hotel for tenant
        $hotel = Hotel::create([
            'name' => 'Default Property',
            'code' => 'DEFAULT',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'check_in_time' => '14:00:00',
            'check_out_time' => '12:00:00',
            'is_active' => true,
        ]);
        
        // Create room types
        $this->createRoomTypes($hotel);
        
        // Create rate plans
        $this->createRatePlans($hotel);
        
        // Create sample rooms
        $this->createRooms($hotel);
        
        // Create roles and permissions for tenant
        $this->createRolesAndPermissions();
        
        // Create default admin user
        $admin = User::create([
            'name' => 'Hotel Admin',
            'email' => 'admin@tenant.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'hotel_id' => $hotel->id,
        ]);
        
        // Assign admin role
        $admin->assignRole('hotel_admin');
    }
    
    /**
     * Create room types.
     */
    protected function createRoomTypes(Hotel $hotel): void
    {
        RoomType::create([
            'hotel_id' => $hotel->id,
            'name' => 'Standard Room',
            'code' => 'STD',
            'description' => 'Comfortable standard room with city view',
            'max_adults' => 2,
            'max_children' => 1,
            'max_occupancy' => 3,
            'base_rate' => 100.00,
            'bed_type' => 'Queen',
            'bed_count' => 1,
            'size_sqm' => 25.00,
            'view_type' => 'City',
            'smoking' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        
        RoomType::create([
            'hotel_id' => $hotel->id,
            'name' => 'Deluxe Room',
            'code' => 'DLX',
            'description' => 'Spacious deluxe room with enhanced amenities',
            'max_adults' => 2,
            'max_children' => 2,
            'max_occupancy' => 4,
            'base_rate' => 150.00,
            'bed_type' => 'King',
            'bed_count' => 1,
            'size_sqm' => 35.00,
            'view_type' => 'Ocean',
            'smoking' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);
        
        RoomType::create([
            'hotel_id' => $hotel->id,
            'name' => 'Executive Suite',
            'code' => 'STE',
            'description' => 'Luxurious suite with separate living area',
            'max_adults' => 3,
            'max_children' => 2,
            'max_occupancy' => 5,
            'base_rate' => 250.00,
            'bed_type' => 'King',
            'bed_count' => 1,
            'size_sqm' => 55.00,
            'view_type' => 'Ocean',
            'smoking' => false,
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }
    
    /**
     * Create rate plans.
     */
    protected function createRatePlans(Hotel $hotel): void
    {
        $standardRoom = RoomType::where('hotel_id', $hotel->id)->where('code', 'STD')->first();
        $deluxeRoom = RoomType::where('hotel_id', $hotel->id)->where('code', 'DLX')->first();
        
        // Best Available Rate (BAR)
        RatePlan::create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $standardRoom->id,
            'name' => 'Best Available Rate',
            'code' => 'BAR',
            'description' => 'Our best flexible rate',
            'type' => 'public',
            'base_rate' => 100.00,
            'is_refundable' => true,
            'min_length_of_stay' => 1,
            'max_length_of_stay' => 30,
            'booking_window_min' => 0,
            'booking_window_max' => 365,
            'includes_breakfast' => false,
            'includes_wifi' => true,
            'includes_parking' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        
        RatePlan::create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $deluxeRoom->id,
            'name' => 'Best Available Rate',
            'code' => 'BAR-DLX',
            'description' => 'Our best flexible rate for Deluxe rooms',
            'type' => 'public',
            'base_rate' => 150.00,
            'is_refundable' => true,
            'min_length_of_stay' => 1,
            'max_length_of_stay' => 30,
            'booking_window_min' => 0,
            'booking_window_max' => 365,
            'includes_breakfast' => false,
            'includes_wifi' => true,
            'includes_parking' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        
        // Non-Refundable Rate
        RatePlan::create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $standardRoom->id,
            'name' => 'Non-Refundable',
            'code' => 'NONREF',
            'description' => 'Save 15% with non-refundable booking',
            'type' => 'public',
            'base_rate' => 85.00, // 15% discount
            'is_refundable' => false,
            'min_length_of_stay' => 1,
            'max_length_of_stay' => 14,
            'booking_window_min' => 7,
            'booking_window_max' => 90,
            'includes_breakfast' => false,
            'includes_wifi' => true,
            'includes_parking' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);
    }
    
    /**
     * Create sample rooms.
     */
    protected function createRooms(Hotel $hotel): void
    {
        $standardRoomType = RoomType::where('hotel_id', $hotel->id)->where('code', 'STD')->first();
        $deluxeRoomType = RoomType::where('hotel_id', $hotel->id)->where('code', 'DLX')->first();
        
        // Create 10 Standard rooms
        for ($i = 101; $i <= 110; $i++) {
            Room::create([
                'hotel_id' => $hotel->id,
                'room_type_id' => $standardRoomType->id,
                'number' => (string) $i,
                'floor' => '1',
                'type' => 'Standard',
                'view_type' => 'City',
                'smoking' => false,
                'status' => 'available',
                'base_rate' => 100.00,
                'notes' => null,
            ]);
        }
        
        // Create 5 Deluxe rooms
        for ($i = 201; $i <= 205; $i++) {
            Room::create([
                'hotel_id' => $hotel->id,
                'room_type_id' => $deluxeRoomType->id,
                'number' => (string) $i,
                'floor' => '2',
                'type' => 'Deluxe',
                'view_type' => 'Ocean',
                'smoking' => false,
                'status' => 'available',
                'base_rate' => 150.00,
                'notes' => null,
            ]);
        }
    }
    
    /**
     * Create roles and permissions.
     */
    protected function createRolesAndPermissions(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Create permissions
        $permissions = [
            // Reservations
            'view reservations',
            'create reservations',
            'update reservations',
            'delete reservations',
            'check-in reservations',
            'check-out reservations',
            
            // Guests
            'view guests',
            'create guests',
            'update guests',
            'delete guests',
            
            // Rooms
            'view rooms',
            'create rooms',
            'update rooms',
            'delete rooms',
            
            // Reports
            'view reports',
            'create reports',
            
            // Accounting
            'view invoices',
            'create invoices',
            'process payments',
            'view ledger',
            
            // Settings
            'manage settings',
            'manage users',
            'manage roles',
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // Create roles
        $roles = [
            'hotel_admin' => $permissions,
            'front_desk' => [
                'view reservations',
                'create reservations',
                'update reservations',
                'check-in reservations',
                'check-out reservations',
                'view guests',
                'create guests',
                'update guests',
                'view rooms',
                'process payments',
            ],
            'housekeeping' => [
                'view rooms',
                'update rooms',
            ],
            'accountant' => [
                'view invoices',
                'create invoices',
                'process payments',
                'view ledger',
                'view reports',
            ],
        ];
        
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }
    }
}
