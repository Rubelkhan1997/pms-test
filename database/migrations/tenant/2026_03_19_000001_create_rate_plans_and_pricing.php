<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rate Plans (Pricing Strategy Layer)
        Schema::create('rate_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();

            // Behavior
            $table->boolean('is_refundable')->default(true);
            $table->string('cancellation_policy')->default('flexible'); // flexible, moderate, strict, non_refundable
            $table->integer('free_cancellation_hours')->default(24);

            // Meal plan
            $table->string('meal_plan')->default('room_only');

            // Restrictions
            $table->integer('min_stay')->nullable();
            $table->integer('max_stay')->nullable();

            // Booking rules
            $table->integer('advance_booking_days')->nullable();
            $table->integer('max_advance_booking_days')->nullable();

            // Visibility
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true);

            // OTA mapping
            $table->string('external_id')->nullable();
            $table->json('channel_mapping')->nullable();

            $table->timestamps();

            $table->index(['is_active', 'is_public']);
        });

        // Rate Plan Room Types (Link)
        Schema::create('rate_plan_room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('base_price', 10, 2)->nullable();
            $table->timestamps();
            $table->unique(['rate_plan_id', 'room_type_id']);
        });

        // Rates (Daily Pricing Engine)
        Schema::create('rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->date('date');

            // Base price (for base occupancy)
            $table->decimal('base_price', 10, 2)->default(0);

            // Occupancy-based pricing
            $table->decimal('single_occupancy_price', 10, 2)->nullable();
            $table->decimal('double_occupancy_price', 10, 2)->nullable();

            // Extra guest pricing
            $table->decimal('extra_adult_price', 10, 2)->nullable();
            $table->decimal('extra_child_price', 10, 2)->nullable();

            // Constraints
            $table->integer('min_stay')->nullable();
            $table->integer('max_stay')->nullable();

            // Stop sell at rate level
            $table->boolean('is_closed')->default(false);
            $table->string('closure_reason')->nullable();

            $table->timestamps();

            $table->unique(['rate_plan_id', 'room_type_id', 'date']);
            $table->index(['rate_plan_id', 'room_type_id', 'date']);
        });

        // Rate Overrides (Manual / Event Pricing)
        Schema::create('rate_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('override_price', 10, 2)->nullable();
            $table->string('operation')->default('set'); // set, increase, decrease
            $table->decimal('value', 8, 2)->nullable(); // % or fixed
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['rate_plan_id', 'room_type_id', 'start_date', 'end_date']);
        });

        // Occupancy Pricing (Granular Control)
        Schema::create('occupancy_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_id')->constrained()->cascadeOnDelete();
            $table->integer('occupancy'); // 1, 2, 3 guests
            $table->decimal('price', 10, 2);
            $table->timestamps();
            $table->unique(['rate_id', 'occupancy']);
        });

        // Extra Guest Charges
        Schema::create('extra_guest_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('guest_type')->default('adult'); // adult, child
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('charge_type')->default('per_night'); // per_night, per_stay
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Derived Rate Plans (Advanced - Child rates derived from parent)
        Schema::create('derived_rate_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_rate_plan_id')->constrained('rate_plans')->cascadeOnDelete();
            $table->foreignId('child_rate_plan_id')->constrained('rate_plans')->cascadeOnDelete();
            $table->string('operation')->default('decrease'); // increase, decrease
            $table->decimal('value', 8, 2); // % or fixed
            $table->string('value_type')->default('percentage'); // percentage, fixed
            $table->timestamps();

            $table->unique(['parent_rate_plan_id', 'child_rate_plan_id']);
        });

        // Promotions (Discount Codes & Offers)
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();

            // Discount type
            $table->string('discount_type')->default('percentage'); // percentage, fixed
            $table->decimal('discount_value', 8, 2)->default(0);

            // Validity
            $table->date('start_date');
            $table->date('end_date');

            // Restrictions
            $table->integer('min_nights')->default(1);
            $table->integer('max_uses')->nullable();
            $table->integer('uses_count')->default(0);
            $table->decimal('min_spend', 10, 2)->nullable();

            // Applicability
            $table->boolean('is_active')->default(true);
            $table->json('applicable_rate_plans')->nullable();
            $table->json('applicable_room_types')->nullable();

            $table->timestamps();

            $table->index(['code', 'is_active']);
        });

        // Promotion Usage Tracking (will add FK to reservations later via separate migration)
        Schema::create('promotion_uses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('reservation_id')->nullable(); // FK added after reservations table created
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['promotion_id', 'reservation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_uses');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('derived_rate_plans');
        Schema::dropIfExists('extra_guest_charges');
        Schema::dropIfExists('occupancy_pricing');
        Schema::dropIfExists('rate_overrides');
        Schema::dropIfExists('rates');
        Schema::dropIfExists('rate_plan_room_types');
        Schema::dropIfExists('rate_plans');
    }
};
