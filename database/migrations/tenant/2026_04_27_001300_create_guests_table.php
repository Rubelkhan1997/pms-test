<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->unique();
            $table->enum('title', ['mr', 'ms', 'mrs', 'dr', 'prof'])->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->char('nationality', 2)->nullable();
            $table->string('language', 10)->nullable();
            $table->enum('id_type', ['passport', 'national_id', 'driving_license', 'other'])->nullable();
            $table->string('id_number')->nullable();
            $table->date('id_expiry')->nullable();
            $table->enum('vip_level', ['standard', 'silver', 'gold', 'platinum'])->default('standard');
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'inactive', 'blacklisted'])->default('active')->index();
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['property_id', 'email']);
            $table->index(['property_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
