<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('number', 10);
            $table->string('floor', 10)->nullable();
            $table->enum('status', ['available', 'occupied', 'maintenance', 'dirty', 'blocked'])->default('available');
            $table->enum('cleaning_status', ['clean', 'dirty', 'inspecting'])->default('clean');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['property_id', 'number']);
            $table->index('property_id');
            $table->index('room_type_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};