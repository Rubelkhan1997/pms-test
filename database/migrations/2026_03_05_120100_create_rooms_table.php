<?php

declare(strict_types=1);

use App\Enums\RoomStatus;
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
        Schema::create('rooms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('number', 20);
            $table->string('floor', 10)->nullable();
            $table->string('type', 50);
            $table->enum('status', array_map(static fn (RoomStatus $item): string => $item->value, RoomStatus::cases()))
                ->default(RoomStatus::Available->value)
                ->index();
            $table->decimal('base_rate', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['hotel_id', 'number']);
            $table->index(['hotel_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
