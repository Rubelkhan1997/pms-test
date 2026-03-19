<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buildings
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['hotel_id', 'is_active']);
        });

        // Floors
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('building_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->integer('level')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'building_id']);
        });

        // Amenities
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('category')->nullable(); // room, hotel, service
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['hotel_id', 'is_active']);
        });

        // Room Types (Sellable Entity)
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();

            // Identification
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->nullable();

            // Capacity
            $table->unsignedTinyInteger('base_occupancy')->default(2);
            $table->unsignedTinyInteger('max_occupancy')->default(4);
            $table->unsignedTinyInteger('max_adults')->nullable();
            $table->unsignedTinyInteger('max_children')->nullable();

            // Bed configuration
            $table->string('bed_type')->nullable();
            $table->unsignedTinyInteger('bed_count')->nullable();

            // Size & view
            $table->decimal('room_size', 8, 2)->nullable();
            $table->string('size_unit')->default('sqft');
            $table->string('view_type')->nullable();

            // Pricing base
            $table->decimal('base_price', 10, 2)->nullable();

            // Extra settings
            $table->boolean('is_active')->default(true);
            $table->boolean('is_refundable')->default(true);
            $table->boolean('is_smoking_allowed')->default(false);

            // Media & description
            $table->text('description')->nullable();

            // Channel sync
            $table->string('external_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['hotel_id', 'is_active', 'slug']);
        });

        // Room Type Amenities (Pivot)
        Schema::create('room_type_amenities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->unique(['room_type_id', 'amenity_id']);
            $table->timestamps();
        });

        // Rooms (Physical Units)
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('building_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('floor_id')->nullable()->constrained()->nullOnDelete();

            // Identification
            $table->string('room_number');
            $table->string('room_code')->nullable();

            // Status
            $table->string('status')->default('available');

            // Housekeeping
            $table->timestamp('last_cleaned_at')->nullable();
            $table->timestamp('last_inspected_at')->nullable();

            // Condition
            $table->integer('condition_score')->nullable();

            // Flags
            $table->boolean('is_active')->default(true);
            $table->boolean('is_visible')->default(true);

            // Smart / IoT
            $table->string('lock_system_id')->nullable();
            $table->string('iot_device_id')->nullable();

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['hotel_id', 'room_number', 'building_id']);
            $table->index(['hotel_id', 'status', 'is_active']);
        });

        // Room Status Logs (Audit Trail)
        Schema::create('room_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('old_status');
            $table->string('new_status');
            $table->timestamp('changed_at');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->index(['room_id', 'changed_at']);
        });

        // Room Blocks (Out of Inventory)
        Schema::create('room_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('reason')->nullable();
            $table->string('block_type')->default('manual'); // manual, ota, vip
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['start_date', 'end_date']);
        });

        // Room Maintenance
        Schema::create('room_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('issue_type');
            $table->text('description');
            $table->date('reported_at');
            $table->date('resolved_at')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->timestamps();
            
            $table->index(['status', 'reported_at']);
        });

        // Room Images
        Schema::create('room_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            $table->index(['room_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_images');
        Schema::dropIfExists('room_maintenance');
        Schema::dropIfExists('room_blocks');
        Schema::dropIfExists('room_status_logs');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_type_amenities');
        Schema::dropIfExists('room_types');
        Schema::dropIfExists('amenities');
        Schema::dropIfExists('floors');
        Schema::dropIfExists('buildings');
    }
};
