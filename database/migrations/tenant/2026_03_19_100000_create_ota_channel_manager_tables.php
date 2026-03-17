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
     * Creates OTA/Channel Manager tables for multi-channel distribution.
     */
    public function up(): void
    {
        // OTA providers (Booking.com, Expedia, etc.)
        Schema::create('ota_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Booking.com, Expedia, Agoda
            $table->string('code')->unique(); // booking, expedia, agoda
            $table->string('api_type'); // xml, json, rest, soap
            $table->string('api_url')->nullable();
            $table->json('credentials')->nullable(); // Encrypted API credentials
            $table->boolean('is_active')->default(true);
            $table->boolean('supports_push')->default(true); // Can push availability/rates
            $table->boolean('supports_pull')->default(true); // Can pull reservations
            $table->json('configuration')->nullable(); // Provider-specific config
            $table->timestamps();
            
            $table->index(['code', 'is_active']);
        });
        
        // Hotel-OTA connections (each hotel can connect to multiple OTAs)
        Schema::create('hotel_ota_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ota_provider_id')->constrained()->cascadeOnDelete();
            $table->string('property_id')->nullable(); // Property ID on OTA
            $table->string('hotel_id_ota')->nullable(); // OTA's hotel ID
            $table->json('credentials')->nullable(); // Hotel-specific encrypted credentials
            $table->string('status')->default('pending'); // pending, active, suspended, error
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->integer('sync_retry_count')->default(0);
            $table->json('settings')->nullable(); // Connection-specific settings
            $table->timestamps();
            
            $table->unique(['hotel_id', 'ota_provider_id']);
            $table->index(['status', 'last_sync_at']);
        });
        
        // Room mapping (our rooms ↔ OTA rooms)
        Schema::create('ota_room_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ota_provider_id')->constrained()->cascadeOnDelete();
            $table->string('ota_room_id'); // Room ID on OTA
            $table->string('ota_room_type_id')->nullable(); // Room type ID on OTA
            $table->string('room_name_ota')->nullable(); // Room name on OTA
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['hotel_id', 'room_type_id', 'ota_provider_id', 'ota_room_id']);
            $table->index(['ota_provider_id', 'ota_room_id']);
        });
        
        // Rate plan mapping (our rates ↔ OTA rates)
        Schema::create('ota_rate_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ota_provider_id')->constrained()->cascadeOnDelete();
            $table->string('ota_rate_plan_id'); // Rate plan ID on OTA
            $table->string('rate_plan_name_ota')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['hotel_id', 'rate_plan_id', 'ota_provider_id', 'ota_rate_plan_id']);
        });
        
        // Sync queue (jobs to sync to OTAs)
        Schema::create('ota_sync_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ota_provider_id')->constrained()->cascadeOnDelete();
            $table->string('sync_type'); // availability, rates, reservations, rooms
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->json('payload')->nullable(); // Data to sync
            $table->integer('attempts')->default(0);
            $table->timestamp('available_at')->nullable(); // When job can be processed
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'available_at']);
            $table->index(['hotel_id', 'ota_provider_id', 'status']);
        });
        
        // Sync logs (audit trail for all sync operations)
        Schema::create('ota_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ota_provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('connection_id')->nullable()->constrained('hotel_ota_connections')->nullOnDelete();
            $table->string('sync_type'); // availability, rates, reservations, rooms
            $table->string('direction'); // push (to OTA), pull (from OTA)
            $table->boolean('success')->default(false);
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->integer('response_code')->nullable();
            $table->text('error_message')->nullable();
            $table->decimal('execution_time', 10, 3)->nullable(); // milliseconds
            $table->timestamp('synced_at');
            $table->timestamps();
            
            $table->index(['hotel_id', 'ota_provider_id', 'synced_at']);
            $table->index(['sync_type', 'direction', 'success']);
        });
        
        // OTA reservations (reservations from OTAs)
        Schema::create('ota_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ota_provider_id')->constrained()->cascadeOnDelete();
            $table->string('ota_reservation_id'); // Reservation ID on OTA
            $table->string('confirmation_number')->nullable();
            $table->string('guest_name');
            $table->string('guest_email')->nullable();
            $table->string('guest_phone')->nullable();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->integer('rooms')->default(1);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('confirmed'); // confirmed, cancelled, modified, no_show
            $table->json('raw_data')->nullable(); // Original OTA data
            $table->json('imported_data')->nullable(); // Data imported to PMS
            $table->boolean('is_imported')->default(false);
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
            
            $table->unique(['ota_provider_id', 'ota_reservation_id']);
            $table->index(['hotel_id', 'check_in_date']);
            $table->index(['status', 'is_imported']);
        });
        
        // Channel inventory allocation (how many rooms per channel)
        Schema::create('channel_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ota_provider_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('allocated_rooms')->default(0);
            $table->integer('booked_rooms')->default(0);
            $table->boolean('is_closed')->default(false); // Closed to arrival
            $table->timestamps();
            
            $table->unique(['hotel_id', 'room_type_id', 'ota_provider_id', 'date']);
            $table->index(['date', 'is_closed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_allocations');
        Schema::dropIfExists('ota_reservations');
        Schema::dropIfExists('ota_sync_logs');
        Schema::dropIfExists('ota_sync_queue');
        Schema::dropIfExists('ota_rate_mappings');
        Schema::dropIfExists('ota_room_mappings');
        Schema::dropIfExists('hotel_ota_connections');
        Schema::dropIfExists('ota_providers');
    }
};
