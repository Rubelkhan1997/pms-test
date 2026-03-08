<?php

declare(strict_types=1);

use App\Enums\POSOrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pos_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('outlet', 100)->index();
            $table->enum('status', array_map(static fn (POSOrderStatus $item): string => $item->value, POSOrderStatus::cases()))
                ->default(POSOrderStatus::Draft->value)
                ->index();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['hotel_id', 'outlet']);
        });

        Schema::create('pos_menu_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category', 100)->index();
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_menu_items');
        Schema::dropIfExists('pos_orders');
    }
};
