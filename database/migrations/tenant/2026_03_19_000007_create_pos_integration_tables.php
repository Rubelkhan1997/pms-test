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

        // ========== POS SUPPLIERS (Must be before inventory & purchase orders) ==========
        Schema::create('pos_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('payment_terms')->default('30 days');
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['code', 'is_active']);
        });

        // ========== POS CATEGORIES ==========
        Schema::create('pos_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('pos_categories')->nullOnDelete();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['outlet_id', 'is_active']);
        });

        // ========== POS MENU ITEMS ==========
        Schema::create('pos_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('pos_categories')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_available')->default(true);
            $table->integer('prep_time_minutes')->default(0);
            $table->timestamps();

            $table->index(['outlet_id', 'is_active']);
        });

        // ========== POS RECIPES ==========
        Schema::create('pos_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('instructions')->nullable();
            $table->integer('prep_time_minutes')->default(0);
            $table->integer('cook_time_minutes')->default(0);
            $table->integer('servings')->default(1);
            $table->decimal('cost_per_serving', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->decimal('food_cost_percent', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['outlet_id', 'is_active']);
        });

        // ========== POS INVENTORY ITEMS ==========
        Schema::create('pos_inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('category_id')->nullable()->constrained('pos_categories')->nullOnDelete();
            $table->string('unit');
            $table->decimal('current_stock', 10, 3)->default(0);
            $table->decimal('min_stock', 10, 3)->default(0);
            $table->decimal('max_stock', 10, 3)->default(0);
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('last_purchase_price', 10, 2)->default(0);
            $table->date('last_purchase_date')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('pos_suppliers')->nullOnDelete();
            $table->boolean('track_inventory')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['outlet_id', 'is_active']);
        });

        // ========== POS RECIPE INGREDIENTS ==========
        Schema::create('pos_recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('pos_recipes')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('pos_inventory_items')->cascadeOnDelete();
            $table->decimal('quantity', 10, 3);
            $table->string('unit');
            $table->decimal('waste_percent', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['recipe_id', 'inventory_item_id']);
        });

        // ========== POS PURCHASE ORDERS ==========
        Schema::create('pos_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('pos_suppliers')->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->date('order_date');
            $table->date('expected_delivery_date');
            $table->date('delivery_date')->nullable();
            $table->string('status')->default('draft');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['outlet_id', 'status']);
        });

        // ========== POS PURCHASE ORDER ITEMS ==========
        Schema::create('pos_purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('pos_purchase_orders')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('pos_inventory_items')->cascadeOnDelete();
            $table->decimal('quantity_ordered', 10, 3);
            $table->decimal('quantity_received', 10, 3)->default(0);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['purchase_order_id']);
        });

        // ========== POS STOCK MOVEMENTS ==========
        Schema::create('pos_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('pos_inventory_items')->cascadeOnDelete();
            $table->string('movement_type');
            $table->decimal('quantity_in', 10, 3)->default(0);
            $table->decimal('quantity_out', 10, 3)->default(0);
            $table->decimal('quantity_after', 10, 3);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['outlet_id', 'inventory_item_id', 'created_at']);
        });

        // ========== POS WASTE TRACKING ==========
        Schema::create('pos_waste_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('pos_inventory_items')->cascadeOnDelete();
            $table->decimal('quantity', 10, 3);
            $table->string('reason');
            $table->text('description')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['outlet_id', 'created_at']);
        });

        // ========== POS STOCK TRANSFERS ==========
        Schema::create('pos_stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('to_outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->string('transfer_number')->unique();
            $table->date('transfer_date');
            $table->string('status')->default('pending');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['outlet_id', 'status']);
        });

        // ========== POS STOCK TRANSFER ITEMS ==========
        Schema::create('pos_stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('pos_stock_transfers')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('pos_inventory_items')->cascadeOnDelete();
            $table->decimal('quantity', 10, 3);
            $table->decimal('quantity_received', 10, 3)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['stock_transfer_id']);
        });
    }

    public function down(): void
    {
        // POS Inventory tables
        Schema::dropIfExists('pos_stock_transfer_items');
        Schema::dropIfExists('pos_stock_transfers');
        Schema::dropIfExists('pos_waste_tracking');
        Schema::dropIfExists('pos_stock_movements');
        Schema::dropIfExists('pos_purchase_order_items');
        Schema::dropIfExists('pos_purchase_orders');
        Schema::dropIfExists('pos_suppliers');
        Schema::dropIfExists('pos_inventory_items');
        Schema::dropIfExists('pos_recipe_ingredients');
        Schema::dropIfExists('pos_recipes');
        Schema::dropIfExists('pos_menu_items');
        Schema::dropIfExists('pos_categories');

        // Original POS tables
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
