<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('landlord')->create('subscription_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('name');                         // "Starter", "Pro", "Enterprise"
            $table->string('slug')->unique();
            $table->unsignedInteger('property_limit')->default(1);
            $table->unsignedInteger('room_limit')->default(50);
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_annual', 10, 2)->default(0);
            $table->boolean('trial_enabled')->default(false);
            $table->unsignedInteger('trial_days')->default(14);
            $table->json('modules_included')->nullable();   // ["pms","housekeeping"]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('subscription_plans');
    }
};
