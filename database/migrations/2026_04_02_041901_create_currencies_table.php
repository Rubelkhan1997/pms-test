<?php

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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Currency code (USD, BDT, etc.)
            $table->string('name')->nullable(); // Currency name (US Dollar, Bangladesh Taka, etc.)
            $table->string('symbol')->nullable(); // Currency symbol ($, ৳, etc.)
            $table->string('flag')->nullable(); // Currency symbol ($, ৳, etc.)
            $table->decimal('conversion_rate', 15, 6)->default(0.0); // Rate compared to USD
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();
       });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
