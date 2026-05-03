<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charge_codes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 20);
            $table->string('name');
            $table->enum('type', ['revenue', 'tax', 'fee', 'discount'])->default('revenue');
            $table->decimal('default_amount', 10, 2)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['property_id', 'code']);
            $table->index('property_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charge_codes');
    }
};
