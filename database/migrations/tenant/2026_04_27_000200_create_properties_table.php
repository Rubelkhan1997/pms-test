<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['hotel', 'resort', 'apartment', 'villa', 'hostel'])->default('hotel');
            $table->unsignedTinyInteger('star_rating')->nullable();
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('featured_image_path')->nullable();
            $table->json('gallery_paths')->nullable();
            $table->unsignedInteger('number_of_rooms')->default(0);
            $table->char('country', 2)->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('area')->nullable();
            $table->string('street')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('timezone')->default('UTC');
            $table->char('currency', 3)->default('USD');
            $table->time('check_in_time')->default('14:00:00');
            $table->time('check_out_time')->default('12:00:00');
            $table->json('child_policy')->nullable();
            $table->json('pet_policy')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->date('business_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
