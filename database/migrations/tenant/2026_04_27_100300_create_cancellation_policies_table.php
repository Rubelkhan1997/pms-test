<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cancellation_policies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('deadline_days')->default(0);
            $table->enum('deadline_type', ['days', 'hours'])->default('days');
            $table->decimal('cancellation_charge_percent', 5, 2)->default(100);
            $table->boolean('no_show_charge_percent')->default(100);
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->index('property_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancellation_policies');
    }
};