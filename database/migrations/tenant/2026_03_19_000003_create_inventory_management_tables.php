<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Inventories (THE HEART - Daily Availability)
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->date('date');

            // Core counts
            $table->integer('total_rooms')->default(0);
            $table->integer('sold_rooms')->default(0);
            $table->integer('reserved_rooms')->default(0);

            // Derived (optimized - but always recalculate in critical operations)
            $table->integer('available_rooms')->default(0);

            // Overbooking control
            $table->integer('overbooking_limit')->default(0);

            // Status flags
            $table->boolean('is_stop_sell')->default(false);

            $table->timestamps();

            $table->unique(['room_type_id', 'date']);
            $table->index(['room_type_id', 'date']);
        });

        // Inventory Logs (Audit Trail)
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->date('date');

            $table->string('action'); // booking, cancellation, manual_update, ota_sync
            $table->integer('change'); // +2, -1
            $table->integer('before');
            $table->integer('after');

            $table->unsignedBigInteger('reference_id')->nullable(); // booking_id
            $table->string('reference_type')->nullable(); // App\Models\Reservation

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['room_type_id', 'date']);
            $table->index(['reference_id', 'reference_type']);
        });

        // Allotments (Channel Allocation)
        Schema::create('allotments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('channel'); // booking_com, agoda, expedia
            $table->date('date');

            $table->integer('allocated_rooms')->default(0);
            $table->integer('sold_rooms')->default(0);

            $table->timestamps();

            $table->unique(['room_type_id', 'channel', 'date']);
            $table->index(['room_type_id', 'date']);
        });

        // Stop Sells (Hard Close)
        Schema::create('stop_sells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['room_type_id', 'start_date', 'end_date']);
        });

        // Min Stay Rules
        Schema::create('min_stay_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('min_nights')->default(1);
            $table->timestamps();

            $table->unique(['room_type_id', 'date']);
        });

        // Max Stay Rules
        Schema::create('max_stay_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('max_nights')->nullable();
            $table->timestamps();

            $table->unique(['room_type_id', 'date']);
        });

        // Closed to Arrival/Departure
        Schema::create('closed_to_arrival_departure', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->boolean('closed_to_arrival')->default(false);
            $table->boolean('closed_to_departure')->default(false);
            $table->timestamps();

            $table->unique(['room_type_id', 'date']);
        });

        // Inventory Snapshots (for analytics)
        Schema::create('inventory_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->date('date');

            // Snapshot data
            $table->integer('total_rooms')->default(0);
            $table->integer('available_rooms')->default(0);
            $table->integer('booked_rooms')->default(0);
            $table->decimal('avg_rate', 10, 2)->default(0);
            $table->decimal('occupancy_rate', 5, 2)->default(0);

            $table->timestamp('captured_at');
            $table->timestamps();

            $table->unique(['room_type_id', 'date', 'captured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_snapshots');
        Schema::dropIfExists('closed_to_arrival_departure');
        Schema::dropIfExists('max_stay_rules');
        Schema::dropIfExists('min_stay_rules');
        Schema::dropIfExists('stop_sells');
        Schema::dropIfExists('allotments');
        Schema::dropIfExists('inventory_logs');
        Schema::dropIfExists('inventories');
    }
};
