<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folio_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('folio_id')->constrained('folios')->cascadeOnDelete();
            $table->foreignId('charge_code_id')->nullable()->constrained('charge_codes')->nullOnDelete();
            $table->foreignId('reservation_room_id')->nullable()->constrained('reservation_rooms')->nullOnDelete();
            $table->foreignId('pos_order_id')->nullable()->constrained('pos_orders')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['charge', 'credit', 'payment', 'transfer', 'adjustment', 'tax'])->index();
            $table->string('description');
            $table->date('date');
            $table->decimal('quantity', 8, 2)->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('amount', 12, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index(['folio_id', 'date']);
            $table->index(['folio_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folio_transactions');
    }
};
