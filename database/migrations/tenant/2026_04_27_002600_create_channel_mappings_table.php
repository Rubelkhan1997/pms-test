<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('channel_id')->constrained('channels')->cascadeOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained('room_types')->nullOnDelete();
            $table->foreignId('rate_plan_id')->nullable()->constrained('rate_plans')->nullOnDelete();
            $table->string('channel_room_id')->nullable();
            $table->string('channel_rate_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['channel_id', 'room_type_id', 'rate_plan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_mappings');
    }
};
