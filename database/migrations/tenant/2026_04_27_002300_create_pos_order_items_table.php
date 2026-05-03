<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('pos_orders')->cascadeOnDelete();
            $table->foreignId('menu_item_id')->nullable()->constrained('pos_menu_items')->nullOnDelete();
            $table->string('name');
            $table->decimal('quantity', 8, 2)->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_order_items');
    }
};
