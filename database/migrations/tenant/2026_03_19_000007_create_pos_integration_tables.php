<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Outlets (Restaurant, Bar, Spa, etc.)
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('type')->default('restaurant'); // restaurant, bar, spa, minibar, gift_shop, other
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['code', 'is_active']);
        });

        // POS Terminals
        Schema::create('pos_terminals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('identifier')->nullable(); // Terminal ID, IP address
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['outlet_id', 'is_active']);
        });

        // POS Payment Methods
        Schema::create('pos_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Visa, MasterCard, Cash, etc.
            $table->string('code')->unique();
            $table->string('type')->default('cash'); // cash, card, digital, voucher
            $table->boolean('allows_room_charge')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['code', 'is_active']);
        });

        // POS Mappings (Map POS items to PMS revenue categories)
        Schema::create('pos_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('pos_item_code');
            $table->string('pos_item_name')->nullable();
            $table->string('revenue_category'); // food, beverage, spa, minibar, other
            $table->string('gl_account')->nullable(); // General Ledger account
            $table->boolean('is_taxable')->default(true);
            $table->decimal('tax_rate', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['pos_item_code']);
        });

        // POS Checks (Bill Header)
        Schema::create('pos_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('terminal_id')->nullable()->constrained('pos_terminals')->nullOnDelete();
            $table->string('check_number')->unique();
            $table->string('table_number')->nullable();
            $table->integer('covers')->default(1); // Number of guests

            // Guest/Room linkage
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->string('guest_room')->nullable();

            // Financials
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);

            // Status
            $table->string('status')->default('open'); // open, closed, voided, transferred

            // Timestamps
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'opened_at']);
            $table->index(['reservation_id', 'room_id']);
        });

        // POS Check Items (Line Items)
        Schema::create('pos_check_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('check_id')->constrained('pos_checks')->cascadeOnDelete();
            $table->string('item_name');
            $table->string('item_code')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->text('special_instructions')->nullable();
            $table->string('revenue_category')->default('food');
            $table->boolean('is_posted_to_pms')->default(false);
            $table->timestamps();

            $table->index(['check_id', 'is_posted_to_pms']);
        });

        // POS Transactions (Payments)
        Schema::create('pos_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('check_id')->constrained('pos_checks')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->foreignId('payment_method_id')->nullable()->constrained('pos_payment_methods')->nullOnDelete();
            $table->string('payment_type')->default('cash'); // cash, card, room_charge, voucher
            $table->string('card_type')->nullable(); // visa, mastercard, etc.
            $table->string('card_last_four')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->string('authorization_code')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['check_id', 'payment_type']);
        });

        // POS Postings (CRITICAL - Tracks POS -> PMS charge posting)
        Schema::create('pos_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('check_id')->constrained('pos_checks')->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('folio_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('folio_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('revenue_category')->default('food');
            $table->string('status')->default('pending'); // pending, posted, failed, voided
            $table->text('error_message')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['reservation_id', 'status']);
        });

        // POS Sync Logs
        Schema::create('pos_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // check_sync, posting, payment_sync
            $table->string('action')->nullable(); // create, update, void
            $table->string('status')->default('pending'); // success, failed, timeout
            $table->foreignId('outlet_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('check_id')->nullable()->constrained('pos_checks')->nullOnDelete();
            $table->foreignId('posting_id')->nullable()->constrained('pos_postings')->nullOnDelete();
            $table->text('request')->nullable();
            $table->text('response')->nullable();
            $table->integer('response_code')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->timestamps();

            $table->index(['type', 'status', 'created_at']);
        });

        // POS Revenue Summary (Daily totals)
        Schema::create('pos_revenue_summary', function (Blueprint $table) {
            $table->id();
            $table->date('business_date');
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->decimal('gross_sales', 12, 2)->default(0);
            $table->decimal('discounts', 12, 2)->default(0);
            $table->decimal('net_sales', 12, 2)->default(0);
            $table->decimal('tax_collected', 12, 2)->default(0);
            $table->decimal('service_charges', 12, 2)->default(0);
            $table->decimal('cash_payments', 12, 2)->default(0);
            $table->decimal('card_payments', 12, 2)->default(0);
            $table->decimal('room_charges', 12, 2)->default(0);
            $table->decimal('other_payments', 12, 2)->default(0);
            $table->integer('total_checks')->default(0);
            $table->integer('total_covers')->default(0);
            $table->json('breakdown')->nullable(); // By category, payment method, etc.
            $table->timestamps();

            $table->unique(['business_date', 'outlet_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('pos_revenue_summary');
        Schema::dropIfExists('pos_sync_logs');
        Schema::dropIfExists('pos_postings');
        Schema::dropIfExists('pos_transactions');
        Schema::dropIfExists('pos_check_items');
        Schema::dropIfExists('pos_checks');
        Schema::dropIfExists('pos_mappings');
        Schema::dropIfExists('pos_payment_methods');
        Schema::dropIfExists('pos_terminals');
        Schema::dropIfExists('outlets');
    }
};
