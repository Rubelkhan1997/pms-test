<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guest Profiles (Master Guest Profile)
        Schema::create('guest_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->nullable();
            $table->string('status')->default('active');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('nationality')->nullable();
            $table->string('id_type')->nullable(); // passport, nid, driving_license
            $table->string('id_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->boolean('is_vip')->default(false);
            $table->json('preferences')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['hotel_id', 'status']);
            $table->index(['email', 'phone']);
            $table->index(['reference']);
        });

        // Reservations (Master Record)
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained()->nullOnDelete();

            // Identification
            $table->string('reservation_number')->unique();
            $table->string('reference')->nullable();
            $table->string('external_reference')->nullable(); // OTA ID

            // Source
            $table->string('channel')->nullable();
            $table->string('source')->default('direct'); // direct, booking_com, agoda, expedia, walk_in, gds
            $table->string('market_segment')->nullable();
            $table->string('channel_id')->nullable();

            // Status lifecycle
            $table->string('status')->default('pending'); // pending, confirmed, checked_in, checked_out, cancelled, no_show

            // Dates
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->timestamp('actual_check_in')->nullable();
            $table->timestamp('actual_check_out')->nullable();

            // Guests
            $table->unsignedTinyInteger('adults')->default(2);
            $table->unsignedTinyInteger('children')->default(0);

            // Financial summary
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
            $table->decimal('taxes', 12, 2)->default(0);
            $table->decimal('discounts', 12, 2)->default(0);

            // Currency
            $table->string('currency', 3)->default('USD');

            // Payment status
            $table->string('payment_status')->default('unpaid'); // unpaid, partial, paid, refunded

            // Links
            $table->foreignId('guest_profile_id')->nullable()->constrained('guest_profiles')->nullOnDelete(); // primary guest
            $table->foreignId('rate_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('guest_profiles')->nullOnDelete(); // travel agent
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();

            // Meta
            $table->text('remarks')->nullable();
            $table->text('internal_notes')->nullable();
            $table->json('special_requests')->nullable();
            $table->json('meta')->nullable();

            // Cancellation
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();

            // No-show
            $table->timestamp('no_show_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['hotel_id', 'status']);
            $table->index(['status', 'check_in_date']);
            $table->index(['check_in_date', 'check_out_date']);
            $table->index(['reservation_number']);
        });

        // Reservation Rooms (Per Room Allocation - Multi-room bookings)
        Schema::create('reservation_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete(); // assigned at check-in
            $table->foreignId('rate_plan_id')->constrained()->cascadeOnDelete();

            // Stay details
            $table->date('check_in_date');
            $table->date('check_out_date');

            // Occupancy
            $table->integer('adults')->default(2);
            $table->integer('children')->default(0);

            // Pricing snapshot (CRITICAL - never recalculate old bookings)
            $table->decimal('price_per_night', 10, 2);
            $table->decimal('total_price', 12, 2);
            $table->decimal('taxes', 10, 2)->default(0);
            $table->decimal('discounts', 10, 2)->default(0);

            // Status
            $table->string('status')->default('allocated'); // allocated, assigned, checked_out

            $table->timestamps();

            $table->index(['reservation_id', 'status']);
        });

        // Reservation Guests (Multiple guests per reservation)
        Schema::create('reservation_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_profile_id')->constrained('guest_profiles')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['reservation_id', 'guest_profile_id']);
        });

        // Folios (Financial Ledger)
        Schema::create('folios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_profile_id')->nullable()->constrained('guest_profiles')->nullOnDelete();
            $table->string('folio_number')->unique();
            $table->decimal('total_charges', 12, 2)->default(0);
            $table->decimal('total_payments', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('status')->default('open'); // open, closed, void
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'status']);
            $table->index(['reservation_id', 'status']);
        });

        // Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('folio_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('payment_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('method')->default('cash');
            $table->string('status')->default('completed');
            $table->string('transaction_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->string('payment_gateway')->nullable();
            $table->json('payment_details')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'status']);
            $table->index(['reservation_id', 'status']);
            $table->index(['folio_id', 'invoice_id']);
        });

        // Reservation Logs (Audit Trail)
        Schema::create('reservation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->json('changes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['reservation_id', 'created_at']);
        });

        // Check-in Logs
        Schema::create('checkin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->timestamp('checked_in_at');
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // Check-out Logs
        Schema::create('checkout_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->timestamp('checked_out_at');
            $table->foreignId('checked_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('final_amount', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // No-show Logs
        Schema::create('no_show_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->timestamp('marked_at');
            $table->text('reason')->nullable();
            $table->decimal('penalty_amount', 12, 2)->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Add foreign key to promotion_uses.reservation_id (created in earlier migration)
        Schema::table('promotion_uses', function (Blueprint $table) {
            $table->foreign('reservation_id')->references('id')->on('reservations')->cascadeOnDelete();
        });

        Schema::table('guest_profiles', function (Blueprint $table) {
            $table->foreign('agent_id')->references('id')->on('guest_profiles')->nullOnDelete();
        });

        Schema::table('stored_credit_cards', function (Blueprint $table) {
            $table->foreign('guest_profile_id')->references('id')->on('guest_profiles')->nullOnDelete();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('reservation_id')->references('id')->on('reservations')->nullOnDelete();
            $table->foreign('folio_id')->references('id')->on('folios')->nullOnDelete();
            $table->foreign('guest_profile_id')->references('id')->on('guest_profiles')->nullOnDelete();
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->foreign('payment_id')->references('id')->on('payments')->nullOnDelete();
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
            $table->foreign('folio_id')->references('id')->on('folios')->nullOnDelete();
        });

        Schema::table('adjustments', function (Blueprint $table) {
            $table->foreign('reservation_id')->references('id')->on('reservations')->nullOnDelete();
            $table->foreign('folio_id')->references('id')->on('folios')->nullOnDelete();
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
            $table->foreign('reservation_id')->references('id')->on('reservations')->nullOnDelete();
        });

        Schema::table('folio_transactions', function (Blueprint $table) {
            $table->foreign('folio_id')->references('id')->on('folios')->cascadeOnDelete();
            $table->foreign('reservation_id')->references('id')->on('reservations')->nullOnDelete();
            $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
        });

        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->foreign('folio_transaction_id')->references('id')->on('folio_transactions')->nullOnDelete();
            $table->foreign('reservation_id')->references('id')->on('reservations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropForeign(['folio_transaction_id']);
            $table->dropForeign(['reservation_id']);
        });

        Schema::table('folio_transactions', function (Blueprint $table) {
            $table->dropForeign(['folio_id']);
            $table->dropForeign(['reservation_id']);
            $table->dropForeign(['room_id']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['room_id']);
            $table->dropForeign(['reservation_id']);
        });

        Schema::table('adjustments', function (Blueprint $table) {
            $table->dropForeign(['reservation_id']);
            $table->dropForeign(['folio_id']);
            $table->dropForeign(['invoice_id']);
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['folio_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['reservation_id']);
            $table->dropForeign(['folio_id']);
            $table->dropForeign(['guest_profile_id']);
        });

        Schema::table('stored_credit_cards', function (Blueprint $table) {
            $table->dropForeign(['guest_profile_id']);
        });

        Schema::table('guest_profiles', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
        });

        Schema::table('promotion_uses', function (Blueprint $table) {
            $table->dropForeign(['reservation_id']);
        });

        Schema::dropIfExists('no_show_logs');
        Schema::dropIfExists('checkout_logs');
        Schema::dropIfExists('checkin_logs');
        Schema::dropIfExists('reservation_logs');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('folios');
        Schema::dropIfExists('reservation_guests');
        Schema::dropIfExists('reservation_rooms');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('guest_profiles');
    }
};
