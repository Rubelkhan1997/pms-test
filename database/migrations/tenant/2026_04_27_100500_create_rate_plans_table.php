<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate.Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pricing_profile_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('cancellation_policy_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->string('description')->nullable();
            $table->decimal('base_rate', 10, 2)->default(0);
            $table->decimal('extra_adult_rate', 10, 2)->default(0);
            $table->decimal('extra_child_rate', 10, 2)->default(0);
            $table->decimal('weekend_factor', 4, 2)->default(1.00);
            $table->decimal('occupancy_factor', 4, 2)->default(1.00);
            $table->boolean('is_dynamic')->default(false);
            $table->boolean('is_direct')->default(true);
            $table->boolean('is_ota')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['property_id', 'room_type_id', 'code']);
            $table->index('property_id');
            $table->index('room_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_plans');
    }
};