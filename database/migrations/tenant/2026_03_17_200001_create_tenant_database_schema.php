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
     * These migrations run on EACH TENANT DATABASE.
     * This contains all hotel-specific data, isolated per tenant.
     */
    public function up(): void
    {
        // Hotels (property information for this tenant)
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('timezone')->default('UTC');
            $table->string('currency')->default('USD');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->time('check_in_time')->default('14:00:00');
            $table->time('check_out_time')->default('12:00:00');
            $table->string('logo_path')->nullable();
            $table->text('description')->nullable();
            $table->json('amenities')->nullable();
            $table->json('branding')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Property taxes
        Schema::create('property_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('percentage');
            $table->decimal('rate', 5, 2)->default(0);
            $table->boolean('is_inclusive')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['hotel_id', 'is_active']);
        });
        
        // Rooms
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('number');
            $table->string('floor')->nullable();
            $table->string('type');
            $table->string('status')->default('available');
            $table->decimal('base_rate', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['hotel_id', 'number']);
            $table->index(['hotel_id', 'status']);
        });
        
        // Reservations
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('guest_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('status')->default('draft');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->timestamp('actual_check_in')->nullable();
            $table->timestamp('actual_check_out')->nullable();
            $table->unsignedTinyInteger('adults')->default(1);
            $table->unsignedTinyInteger('children')->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['hotel_id', 'status']);
            $table->index(['hotel_id', 'check_in_date', 'check_out_date']);
        });
        
        // Guest profiles
        Schema::create('guest_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('status')->default('active');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['hotel_id', 'status']);
        });
        
        // Users (tenant-specific users)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['hotel_id', 'email']);
        });
        
        // Add hotel_id to permission tables (for tenant-specific permissions)
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('hotel_id')->nullable()->after('model_id');
            $table->index(['hotel_id']);
        });
        
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('hotel_id')->nullable()->after('model_id');
            $table->index(['hotel_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('users');
        Schema::dropIfExists('guest_profiles');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('property_taxes');
        Schema::dropIfExists('hotels');
    }
};
