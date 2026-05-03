<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table): void {
            $table->id();
            $table->char('code', 3)->unique();
            $table->string('name')->nullable();
            $table->string('symbol', 10)->nullable();
            $table->string('flag', 10)->nullable();
            $table->decimal('conversion_rate', 15, 6)->default(1.000000);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
