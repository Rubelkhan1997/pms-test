<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// FILE: database/migrations/xxxx_xx_xx_create_[TABLE]_table.php

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('[TABLE]', function (Blueprint $table) {
            $table->id();
            
            // [COLUMNS]
            // Example:
            // $table->string('name');
            // $table->string('code')->unique();
            // $table->string('email')->nullable();
            // $table->string('phone')->nullable();
            // $table->text('address')->nullable();
            // $table->enum('status', ['active', 'inactive'])->default('active');
            // $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            // [END_COLUMNS]
            
            $table->timestamps();
            $table->softDeletes(); // Remove if not needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('[TABLE]');
    }
};
