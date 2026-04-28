<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->enum('type', ['room', 'suite', 'cottage', 'villa', 'dormitory'])->default('room');
            $table->string('floor')->nullable();
            $table->unsignedSmallInteger('max_occupancy')->default(2);
            $table->unsignedSmallInteger('adult_occupancy')->default(2);
            $table->unsignedSmallInteger('num_bedrooms')->default(1);
            $table->unsignedSmallInteger('num_bathrooms')->default(1);
            $table->decimal('area_sqm', 8, 2)->nullable();
            $table->json('bed_types')->nullable();
            $table->decimal('base_rate', 10, 2)->default(0);
            $table->json('amenities')->nullable();
            $table->json('gallery_paths')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['property_id', 'code']);
            $table->index('property_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};