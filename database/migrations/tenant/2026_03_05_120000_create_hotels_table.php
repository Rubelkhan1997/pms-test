<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('code')->unique();

            // Multi-tenant (reference to central accounts table - no FK constraint)
            $table->unsignedBigInteger('account_id')->nullable();
            $table->index('account_id');

            // Contact
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Location
            $table->json('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();

            // Localization
            $table->string('timezone')->default('UTC');
            $table->char('currency', 3)->default('USD');

            // Operations
            $table->time('check_in_time')->default('14:00:00');
            $table->time('check_out_time')->default('12:00:00');

            // Tax
            $table->decimal('default_tax_percentage', 5, 2)->default(0);
            $table->boolean('is_tax_inclusive')->default(false);

            // Revenue control
            $table->boolean('allow_overbooking')->default(false);
            $table->integer('overbooking_limit')->default(0);

            // Branding
            $table->string('logo_path')->nullable();
            $table->json('branding')->nullable();

            // Meta
            $table->text('description')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'is_active']);
            $table->index(['account_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
