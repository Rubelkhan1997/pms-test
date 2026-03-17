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
     * Creates room types, features, and enhanced room management tables.
     * These tables go in EACH TENANT database.
     */
    public function up(): void
    {
        // Room types (Standard, Deluxe, Suite, etc.)
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Deluxe, Suite, Standard
            $table->string('code')->unique(); // DLX, STE, STD
            $table->text('description')->nullable();
            $table->integer('max_adults')->default(2);
            $table->integer('max_children')->default(1);
            $table->integer('max_occupancy')->default(3);
            $table->decimal('base_rate', 10, 2)->default(0);
            $table->string('bed_type')->nullable(); // King, Queen, Twin
            $table->integer('bed_count')->default(1);
            $table->decimal('size_sqm', 8, 2)->nullable(); // Room size
            $table->string('view_type')->nullable(); // City, Ocean, Garden
            $table->boolean('smoking')->default(false);
            $table->json('amenities')->nullable(); // Room amenities
            $table->json('photos')->nullable(); // Room photos
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['hotel_id', 'is_active']);
            $table->index(['code']);
        });
        
        // Room features (amenities available in rooms)
        Schema::create('room_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // WiFi, Mini Bar, Safe, TV
            $table->string('category')->nullable(); // Technology, Comfort, Safety
            $table->boolean('is_free')->default(true);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('unit')->default('per_stay'); // per_stay, per_night, per_item
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['hotel_id', 'category']);
        });
        
        // Room type features (which features each room type has)
        Schema::create('room_type_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_feature_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_included')->default(true);
            $table->decimal('price_override', 10, 2)->nullable(); // Override default price
            $table->timestamps();
            
            $table->unique(['room_type_id', 'room_feature_id']);
        });
        
        // Update rooms table to add room_type_id
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('room_type_id')->nullable()->after('hotel_id')->constrained()->nullOnDelete();
            $table->string('view_type')->nullable()->after('type');
            $table->boolean('smoking')->default(false)->after('view_type');
            $table->text('notes')->nullable()->after('status');
            $table->index(['room_type_id', 'status']);
        });
        
        // Room status history (track all status changes)
        Schema::create('room_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->text('reason')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();
            
            $table->index(['room_id', 'changed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_status_history');
        Schema::dropIfExists('room_type_features');
        Schema::dropIfExists('room_features');
        
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['room_type_id']);
            $table->dropColumn(['room_type_id', 'view_type', 'smoking', 'notes']);
        });
        
        Schema::dropIfExists('room_types');
    }
};
