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
     * This migration creates the CENTRAL database schema for tenant management.
     * This database manages all tenants, subscriptions, and billing.
     * Each tenant gets their own isolated database for hotel data.
     */
    public function up(): void
    {
        // Central tenants table - manages all tenants
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // Hotel/Property name
            $table->string('company_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('domain')->unique()->nullable(); // Custom domain
            $table->string('subdomain')->unique()->nullable(); // subdomain.pms.com
            $table->string('database_name')->unique(); // Isolated database name
            $table->string('status')->default('pending'); // pending, active, suspended, cancelled
            $table->string('country')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('currency')->default('USD');
            $table->json('settings')->nullable(); // Tenant-specific settings
            $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'created_at']);
            $table->index(['domain', 'status']);
            $table->index(['subdomain', 'status']);
        });
        
        // Tenant owners (users who own/manage this tenant)
        Schema::create('tenant_owners', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('owner'); // owner, admin, manager
            $table->timestamps();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'user_id']);
        });
        
        // Subscription plans (defined in central DB)
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Starter, Professional, Enterprise
            $table->string('code')->unique(); // starter, professional, enterprise
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->integer('trial_days')->default(14);
            $table->integer('max_properties')->default(1);
            $table->integer('max_rooms')->default(50);
            $table->integer('max_users')->default(10);
            $table->json('features')->nullable(); // Feature flags
            $table->json('limits')->nullable(); // Usage limits
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['code', 'is_active']);
        });
        
        // Tenant subscriptions
        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreignId('subscription_plan_id')->constrained();
            $table->string('status')->default('trial'); // trial, active, cancelled, suspended, expired
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('trial_ends_at')->nullable();
            $table->string('billing_cycle')->default('monthly'); // monthly, yearly
            $table->decimal('amount', 10, 2)->default(0);
            $table->date('last_payment_date')->nullable();
            $table->date('next_billing_date')->nullable();
            $table->string('payment_method')->nullable(); // stripe, paypal, bank_transfer
            $table->string('stripe_id')->nullable(); // Stripe subscription ID
            $table->string('stripe_status')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
            $table->index(['end_date', 'status']);
        });
        
        // Subscription usage tracking
        Schema::create('tenant_usage_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreignId('subscription_id')->constrained('tenant_subscriptions')->cascadeOnDelete();
            $table->string('metric'); // properties, rooms, users, api_calls, storage_mb
            $table->integer('value')->default(0);
            $table->date('recorded_date');
            $table->timestamps();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'subscription_id', 'metric', 'recorded_date']);
        });
        
        // Tenant invoices (for subscription billing)
        Schema::create('tenant_invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreignId('subscription_id')->nullable()->constrained('tenant_subscriptions')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('status')->default('draft'); // draft, sent, paid, overdue, cancelled
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->string('stripe_invoice_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
            $table->index(['invoice_number', 'status']);
        });
        
        // Tenant invoice items
        Schema::create('tenant_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('tenant_invoices')->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('type')->default('charge'); // charge, payment, adjustment
            $table->timestamps();
            
            $table->index(['invoice_id']);
        });
        
        // Tenant payments
        Schema::create('tenant_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreignId('invoice_id')->nullable()->constrained('tenant_invoices')->nullOnDelete();
            $table->string('payment_number')->unique();
            $table->string('method'); // stripe, paypal, bank_transfer, check, cash
            $table->decimal('amount', 10, 2)->default(0);
            $table->date('payment_date');
            $table->string('transaction_id')->nullable(); // Stripe/PayPal transaction ID
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Gateway response, etc.
            $table->timestamps();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'payment_date']);
        });
        
        // System-wide settings
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, json, array
            $table->string('group')->nullable(); // billing, email, sms, general
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['key', 'group']);
        });
        
        // Activity logs for central system
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('event')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('logged_at');
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'logged_at']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('tenant_payments');
        Schema::dropIfExists('tenant_invoice_items');
        Schema::dropIfExists('tenant_invoices');
        Schema::dropIfExists('tenant_usage_records');
        Schema::dropIfExists('tenant_subscriptions');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('tenant_owners');
        Schema::dropIfExists('tenants');
    }
};
