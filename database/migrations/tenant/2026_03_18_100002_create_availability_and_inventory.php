<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates availability and inventory management tables.
     */
    public function up(): void
    {
        // Room availability (daily inventory)
        Schema::create('room_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('total_rooms')->default(0);
            $table->integer('available_rooms')->default(0);
            $table->integer('booked_rooms')->default(0);
            $table->integer('out_of_order')->default(0);
            $table->integer('out_of_inventory')->default(0);
            $table->boolean('is_closed')->default(false); // Closed to arrival
            $table->timestamps();
            
            $table->unique(['hotel_id', 'room_type_id', 'date']);
            $table->index(['hotel_id', 'date']);
        });
        
        // Inventory allocation (for OTAs, channels)
        Schema::create('inventory_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('channel'); // Booking.com, Expedia, Direct
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('allocated_rooms')->default(0);
            $table->integer('booked_rooms')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['hotel_id', 'channel', 'start_date', 'end_date']);
        });
        
        // Overbooking settings
        Schema::create('overbooking_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('overbook_limit')->default(0); // Number of rooms to overbook
            $table->decimal('overbook_percentage', 5, 2)->default(0); // Percentage overbook
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable(); // Seasonal overbooking
            $table->date('end_date')->nullable();
            $table->timestamps();
            
            $table->index(['hotel_id', 'is_active']);
        });
        
        // Update reservations table to add rate_plan_id
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('rate_plan_id')->nullable()->after('room_id')->constrained()->nullOnDelete();
            $table->string('channel')->default('direct')->after('rate_plan_id'); // direct, booking.com, expedia, etc.
            $table->string('market_segment')->nullable()->after('channel'); // leisure, corporate, group
            $table->string('source')->nullable()->after('market_segment'); // Website, Phone, Walk-in
            $table->index(['rate_plan_id', 'status']);
            $table->index(['channel', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['rate_plan_id']);
            $table->dropColumn(['rate_plan_id', 'channel', 'market_segment', 'source']);
        });
        
        Schema::dropIfExists('overbooking_settings');
        Schema::dropIfExists('inventory_allocations');
        Schema::dropIfExists('room_availability');
    }
};
