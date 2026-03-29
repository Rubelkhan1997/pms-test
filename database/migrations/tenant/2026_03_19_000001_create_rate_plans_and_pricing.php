<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rate Plans, Pricing, and Accounting Tables
 *
 * Creates all pricing, revenue, and accounting-related tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ========== REVENUE CATEGORIES ==========
        Schema::create('revenue_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('gl_account')->nullable();
            $table->string('type')->default('revenue');
            $table->boolean('is_taxable')->default(true);
            $table->decimal('default_tax_rate', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('parent_id')->nullable()->constrained('revenue_categories')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['code', 'is_active']);
        });

        // ========== TAXES ==========
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('rate', 5, 2)->default(0);
            $table->string('type')->default('percentage');
            $table->boolean('is_compound')->default(false);
            $table->boolean('is_inclusive')->default(false);
            $table->foreignId('revenue_category_id')->nullable()->constrained()->nullOnDelete();
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['code', 'is_active']);
        });

        // ========== SERVICE CHARGES ==========
        Schema::create('service_charges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('rate', 5, 2)->default(0);
            $table->string('type')->default('percentage');
            $table->boolean('is_taxable')->default(true);
            $table->foreignId('revenue_category_id')->constrained();
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['code', 'is_active']);
        });

        // ========== PAYMENT METHODS ==========
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type')->default('cash');
            $table->boolean('allows_room_charge')->default(false);
            $table->boolean('allows_online_payment')->default(false);
            $table->string('processor')->nullable();
            $table->decimal('processing_fee_percent', 5, 2)->default(0);
            $table->decimal('processing_fee_fixed', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['code', 'is_active']);
        });

        // ========== STORED CREDIT CARDS ==========
        Schema::create('stored_credit_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guest_profile_id')->nullable();
            $table->string('card_token');
            $table->string('card_brand');
            $table->string('card_last_four', 4);
            $table->integer('expiry_month');
            $table->integer('expiry_year');
            $table->string('cardholder_name');
            $table->string('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_zip')->nullable();
            $table->string('billing_country')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['guest_profile_id', 'is_default']);
        });

        // ========== INVOICES ==========
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->unsignedBigInteger('folio_id')->nullable();
            $table->unsignedBigInteger('guest_profile_id')->nullable();
            $table->string('invoice_number')->unique();
            $table->string('status')->default('draft');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'status', 'invoice_date']);
            $table->index(['reservation_id', 'folio_id']);
            $table->index(['guest_profile_id', 'status']);
        });

        // ========== REFUNDS ==========
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('folio_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('reason');
            $table->string('status')->default('pending');
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('refund_reference')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['payment_id', 'status']);
        });

        // ========== ADJUSTMENTS ==========
        Schema::create('adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->unsignedBigInteger('folio_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('adjustment_type');
            $table->decimal('amount', 10, 2);
            $table->foreignId('revenue_category_id')->constrained();
            $table->string('reason');
            $table->text('description');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_posted')->default(false);
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'created_at']);
        });

        // ========== CASHIER SESSIONS ==========
        Schema::create('cashier_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->decimal('opening_balance', 10, 2)->default(0);
            $table->decimal('closing_balance', 10, 2)->default(0);
            $table->decimal('cash_in', 10, 2)->default(0);
            $table->decimal('cash_out', 10, 2)->default(0);
            $table->decimal('expected_balance', 10, 2)->default(0);
            $table->decimal('overage_shortage', 10, 2)->default(0);
            $table->string('status')->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        // ========== PETTY CASH ==========
        Schema::create('petty_cash', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->date('transaction_date');
            $table->string('type');
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->decimal('balance', 10, 2);
            $table->foreignId('category_id')->nullable()->constrained('revenue_categories')->nullOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('receipt_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'transaction_date']);
        });

        // ========== INVOICE ITEMS ==========
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('description');
            $table->foreignId('revenue_category_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->unsignedBigInteger('room_id')->nullable();
            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'date']);
        });

        // ========== FOLIO TRANSACTIONS ==========
        Schema::create('folio_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folio_id')->nullable();
            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->date('transaction_date');
            $table->time('transaction_time')->nullable();
            $table->string('transaction_type');
            $table->string('description');
            $table->foreignId('revenue_category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->unsignedBigInteger('room_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['folio_id', 'transaction_date']);
        });

        // ========== LEDGER ENTRIES ==========
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->unsignedBigInteger('folio_transaction_id')->nullable();
            $table->date('entry_date');
            $table->string('description');
            $table->string('entry_type');
            $table->foreignId('revenue_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('debit_amount', 12, 2)->default(0);
            $table->decimal('credit_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('debit_account')->nullable();
            $table->string('credit_account')->nullable();
            $table->boolean('is_posted')->default(false);
            $table->timestamp('posted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['batch_number', 'entry_date']);
            $table->index(['hotel_id', 'entry_date']);
        });

        // ========== RATE PLANS ==========
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
        // Accounting tables
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('folio_transactions');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('petty_cash');
        Schema::dropIfExists('cashier_sessions');
        Schema::dropIfExists('adjustments');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('stored_credit_cards');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('service_charges');
        Schema::dropIfExists('taxes');
        Schema::dropIfExists('revenue_categories');

        // Pricing tables
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
