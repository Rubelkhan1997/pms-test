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
     * Creates rate plans, seasonal pricing, and rate restrictions.
     */
    public function up(): void
    {
        // Rate plans (BAR, Corporate, Package, etc.)
        Schema::create('rate_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); // Best Available Rate, Corporate, Non-Refundable
            $table->string('code')->unique(); // BAR, CORP, NONREF
            $table->text('description')->nullable();
            $table->string('type')->default('public'); // public, corporate, package, secret
            $table->decimal('base_rate', 10, 2)->default(0);
            $table->boolean('is_refundable')->default(true);
            $table->integer('min_length_of_stay')->default(1);
            $table->integer('max_length_of_stay')->default(30);
            $table->integer('booking_window_min')->default(0); // Days in advance
            $table->integer('booking_window_max')->default(365);
            $table->boolean('includes_breakfast')->default(false);
            $table->boolean('includes_wifi')->default(false);
            $table->boolean('includes_parking')->default(false);
            $table->json('inclusions')->nullable(); // Other inclusions
            $table->json('cancellation_policy')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['hotel_id', 'is_active']);
            $table->index(['room_type_id', 'is_active']);
        });
        
        // Seasonal pricing
        Schema::create('seasonal_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); // High Season, Low Season, Holiday
            $table->date('start_date');
            $table->date('end_date');
            $table->string('type')->default('percentage'); // percentage, fixed, override
            $table->decimal('adjustment', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['hotel_id', 'start_date', 'end_date']);
        });
        
        // Daily rate overrides (specific date pricing)
        Schema::create('daily_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->decimal('rate', 10, 2)->default(0);
            $table->integer('inventory')->default(-1); // -1 = unlimited
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            
            $table->unique(['hotel_id', 'room_type_id', 'rate_plan_id', 'date']);
            $table->index(['hotel_id', 'date']);
        });
        
        // Rate restrictions (CTA, CTD, MLOS, etc.)
        Schema::create('rate_restrictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rate_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('restriction_type'); // CTA, CTD, MLOS, MaxLOS
            $table->integer('value')->default(0); // 0/1 for CTA/CTD, nights for MLOS
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['hotel_id', 'start_date', 'end_date']);
        });
        
        // Blackout dates
        Schema::create('blackout_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->timestamps();
            
            $table->index(['hotel_id', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blackout_dates');
        Schema::dropIfExists('rate_restrictions');
        Schema::dropIfExists('daily_rates');
        Schema::dropIfExists('seasonal_pricing');
        Schema::dropIfExists('rate_plans');
    }
};
