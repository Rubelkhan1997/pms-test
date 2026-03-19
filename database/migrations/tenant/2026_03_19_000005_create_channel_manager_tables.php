<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Channels (OTA Providers)
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Booking.com, Expedia, Agoda
            $table->string('code')->unique(); // booking_com, expedia
            $table->string('api_type')->default('xml'); // xml, json, rest
            $table->string('api_url')->nullable();
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->json('credentials')->nullable(); // encrypted
            $table->boolean('is_active')->default(true);
            $table->boolean('is_test_mode')->default(false);
            $table->integer('rate_limit_per_minute')->default(60);
            $table->integer('timeout_seconds')->default(30);
            $table->timestamps();

            $table->index(['code', 'is_active']);
        });

        // Channel Room Mappings (CRITICAL - Maps your room types to OTA room IDs)
        Schema::create('channel_room_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('external_room_id'); // OTA room type ID
            $table->string('external_room_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['channel_id', 'room_type_id']);
            $table->index(['external_room_id']);
        });

        // Channel Rate Mappings
        Schema::create('channel_rate_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_plan_id')->constrained()->cascadeOnDelete();
            $table->string('external_rate_id'); // OTA rate plan ID
            $table->string('external_rate_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['channel_id', 'rate_plan_id']);
        });

        // Rate Push Queue (VERY IMPORTANT)
        Schema::create('rate_push_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_plan_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('price', 10, 2);
            $table->integer('min_stay')->nullable();
            $table->integer('max_stay')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->string('status')->default('pending'); // pending, processing, success, failed
            $table->integer('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->json('response_data')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['channel_id', 'status', 'created_at']);
            $table->index(['date', 'status']);
        });

        // Inventory Push Queue
        Schema::create('inventory_push_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('available_rooms');
            $table->integer('overbooking_limit')->default(0);
            $table->boolean('is_stop_sell')->default(false);
            $table->string('status')->default('pending'); // pending, processing, success, failed
            $table->integer('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->json('response_data')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['channel_id', 'status', 'created_at']);
            $table->index(['date', 'status']);
        });

        // OTA Reservations (Incoming Data)
        Schema::create('ota_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->string('external_reservation_id')->unique(); // OTA booking ID
            $table->string('confirmation_number')->nullable(); // Our confirmation
            $table->json('payload'); // Raw OTA data
            $table->json('guest_data')->nullable();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('adults')->default(2);
            $table->integer('children')->default(0);
            $table->integer('rooms')->default(1);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('pending'); // pending, processed, failed, cancelled
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete(); // Linked local reservation
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['check_in_date', 'check_out_date']);
        });

        // OTA Webhooks
        Schema::create('ota_webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->string('event_type'); // booking_new, booking_modified, booking_cancelled
            $table->string('external_reservation_id')->nullable();
            $table->json('payload');
            $table->json('headers')->nullable();
            $table->boolean('processed')->default(false);
            $table->foreignId('ota_reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['channel_id', 'processed', 'created_at']);
            $table->index(['external_reservation_id']);
        });

        // Channel Sync Logs (Audit Trail)
        Schema::create('channel_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // rate_push, inventory_push, reservation_pull, webhook
            $table->string('action')->nullable(); // update, create, cancel
            $table->string('status')->default('pending'); // success, failed, timeout
            $table->string('endpoint')->nullable();
            $table->text('request')->nullable(); // Request body
            $table->text('response')->nullable(); // Response body
            $table->integer('response_code')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->foreignId('room_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rate_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->date('sync_date')->nullable();
            $table->timestamps();

            $table->index(['channel_id', 'type', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        // Channel Settings (Per-channel configuration)
        Schema::create('channel_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->string('key'); // e.g., booking_com_sync_interval
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->timestamps();

            $table->unique(['channel_id', 'key']);
        });

        // Overbooking Settings
        Schema::create('overbooking_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained()->cascadeOnDelete(); // null = global
            $table->integer('overbooking_limit')->default(0); // number of rooms
            $table->integer('overbooking_percentage')->default(0); // percentage of inventory
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['room_type_id', 'channel_id', 'start_date', 'end_date']);
        });

        // Channel Rate Differences Log (Track mismatches)
        Schema::create('channel_rate_diff_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_plan_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('local_rate', 10, 2);
            $table->decimal('channel_rate', 10, 2);
            $table->decimal('difference', 10, 2);
            $table->string('status')->default('pending'); // pending, resolved, ignored
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['channel_id', 'status', 'created_at']);
        });

        // Sync Snapshots (Full sync backups for reconciliation)
        Schema::create('channel_sync_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->date('snapshot_date');
            $table->json('inventory_snapshot'); // room_type_id => available_rooms
            $table->json('rate_snapshot'); // room_type_id x rate_plan_id => rates
            $table->integer('total_rooms_synced')->default(0);
            $table->integer('total_rates_synced')->default(0);
            $table->integer('failed_syncs')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['channel_id', 'snapshot_date']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('channel_sync_snapshots');
        Schema::dropIfExists('channel_rate_diff_logs');
        Schema::dropIfExists('overbooking_settings');
        Schema::dropIfExists('channel_settings');
        Schema::dropIfExists('channel_sync_logs');
        Schema::dropIfExists('ota_webhooks');
        Schema::dropIfExists('ota_reservations');
        Schema::dropIfExists('inventory_push_queue');
        Schema::dropIfExists('rate_push_queue');
        Schema::dropIfExists('channel_rate_mappings');
        Schema::dropIfExists('channel_room_mappings');
        Schema::dropIfExists('channels');
    }
};
