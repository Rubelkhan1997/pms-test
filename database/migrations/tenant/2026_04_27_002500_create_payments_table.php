<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('folio_id')->nullable()->constrained('folios')->nullOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('method', ['cash', 'credit_card', 'debit_card', 'bank_transfer', 'ota_collect', 'cheque', 'credit_note', 'complimentary'])->index();
            $table->decimal('amount', 12, 2);
            $table->decimal('exchange_rate', 10, 6)->default(1.000000);
            $table->string('reference')->nullable();
            $table->char('card_last_four', 4)->nullable();
            $table->string('card_brand')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded', 'voided'])->default('pending')->index();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'status']);
            $table->index('folio_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
