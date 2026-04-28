<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('landlord')->create('add_ons', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('pricing_type', ['fixed', 'per_property', 'per_room'])->default('fixed');
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('billing_cycle', ['inherit', 'monthly', 'annual'])->default('inherit');
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('requires_activation')->default(false);
            $table->json('dependencies')->nullable();       // ["pos"] = requires POS add-on
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('landlord')->create('plan_add_ons', function (Blueprint $table): void {
            $table->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->foreignId('add_on_id')->constrained('add_ons')->cascadeOnDelete();
            $table->primary(['plan_id', 'add_on_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('plan_add_ons');
        Schema::connection('landlord')->dropIfExists('add_ons');
    }
};
