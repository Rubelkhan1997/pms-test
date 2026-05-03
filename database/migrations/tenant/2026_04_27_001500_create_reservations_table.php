<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained('channels')->nullOnDelete();
            $table->foreignId('cancellation_policy_id')->nullable()->constrained('cancellation_policies')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->unique();
            $table->enum('source', ['walk_in', 'phone', 'email', 'ota', 'website', 'gds', 'corporate'])->default('walk_in');
            $table->string('channel_reference')->nullable();
            $table->string('group_name')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'])->default('draft')->index();
            $table->date('check_in_date')->index();
            $table->date('check_out_date')->index();
            $table->unsignedTinyInteger('adults')->default(1);
            $table->unsignedTinyInteger('children')->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->text('special_requests')->nullable();
            $table->time('arrival_time')->nullable();
            $table->time('departure_time')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('no_show_at')->nullable();
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['property_id', 'status']);
            $table->index(['property_id', 'check_in_date', 'check_out_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
