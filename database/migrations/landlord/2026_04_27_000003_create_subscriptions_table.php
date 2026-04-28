<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('landlord')->create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans');
            $table->enum('billing_cycle', ['monthly', 'annual'])->default('monthly');
            $table->enum('status', ['trial', 'active', 'past_due', 'cancelled'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->date('current_period_start')->nullable();
            $table->date('current_period_end')->nullable();
            $table->unsignedInteger('property_count')->default(0);
            $table->unsignedInteger('room_count')->default(0);
            $table->json('add_ons')->nullable();
            $table->date('next_invoice_date')->nullable();
            $table->timestamps();
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('subscriptions');
    }
};
