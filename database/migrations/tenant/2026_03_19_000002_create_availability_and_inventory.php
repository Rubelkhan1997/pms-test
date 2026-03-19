<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guests (Master Guest Profile)
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
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
            $table->text('preferences')->nullable(); // json
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['email', 'phone']);
        });

        // Reservations (Master Record)
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('reservation_number')->unique();
            $table->string('external_reference')->nullable(); // OTA ID

            // Source
            $table->string('source')->default('direct'); // direct, booking_com, agoda, expedia, walk_in, gds
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
            $table->foreignId('guest_id')->nullable()->constrained()->nullOnDelete(); // primary guest
            $table->foreignId('rate_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('guests')->nullOnDelete(); // travel agent
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();

            // Meta
            $table->text('remarks')->nullable();
            $table->text('internal_notes')->nullable();
            $table->json('special_requests')->nullable();

            // Cancellation
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();

            // No-show
            $table->timestamp('no_show_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

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
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['reservation_id', 'guest_id']);
        });

        // Folios (Financial Ledger)
        Schema::create('folios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->string('folio_number')->unique();
            $table->decimal('total_charges', 12, 2)->default(0);
            $table->decimal('total_payments', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('status')->default('open'); // open, closed, void
            $table->timestamps();

            $table->index(['reservation_id', 'status']);
        });

        // Folio Transactions (Ledger Entries)
        Schema::create('folio_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folio_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // room_charge, service_charge, tax, discount, payment, refund
            $table->decimal('amount', 12, 2);
            $table->string('description')->nullable();
            $table->date('transaction_date');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference_type')->nullable(); // payment_id, adjustment_id
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['folio_id', 'transaction_date']);
        });

        // Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('method')->default('cash'); // cash, card, bank_transfer, online, mobile
            $table->string('status')->default('completed'); // pending, completed, failed, refunded
            $table->string('transaction_reference')->nullable();
            $table->string('payment_gateway')->nullable(); // stripe, paypal, etc.
            $table->json('payment_details')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['reservation_id', 'status']);
        });

        // Reservation Logs (Audit Trail)
        Schema::create('reservation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->string('action'); // created, updated, confirmed, cancelled, checked_in, checked_out, no_show
            $table->text('description')->nullable();
            $table->json('changes')->nullable(); // before/after snapshot
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
    }

    public function down(): void
    {
        Schema::table('promotion_uses', function (Blueprint $table) {
            $table->dropForeign(['reservation_id']);
        });

        Schema::dropIfExists('no_show_logs');
        Schema::dropIfExists('checkout_logs');
        Schema::dropIfExists('checkin_logs');
        Schema::dropIfExists('reservation_logs');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('folio_transactions');
        Schema::dropIfExists('folios');
        Schema::dropIfExists('reservation_guests');
        Schema::dropIfExists('reservation_rooms');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('guests');
    }
};
