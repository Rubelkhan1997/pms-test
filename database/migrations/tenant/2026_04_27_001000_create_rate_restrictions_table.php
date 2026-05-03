<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_restrictions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_plan_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedSmallInteger('min_stay')->nullable();
            $table->unsignedSmallInteger('max_stay')->nullable();
            $table->unsignedSmallInteger('min_rooms')->default(0);
            $table->unsignedSmallInteger('max_rooms')->nullable();
            $table->boolean('closed')->default(false);
            $table->boolean('closed_to_arrival')->default(false);
            $table->boolean('closed_to_departure')->default(false);
            $table->decimal('rate_override', 10, 2)->nullable();
            $table->text('stop_sell_reason')->nullable();
            $table->timestamps();

            $table->unique(['rate_plan_id', 'date']);
            $table->index(['property_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_restrictions');
    }
};
