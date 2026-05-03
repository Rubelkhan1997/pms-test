<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->enum('target_market', ['business', 'leisure', 'corporate', 'groups', 'all'])->default('all');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['property_id', 'code']);
            $table->index('property_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_profiles');
    }
};
