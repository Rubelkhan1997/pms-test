<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->unique();
            $table->enum('status', ['active', 'inactive', 'terminated', 'on_leave'])->default('active')->index();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable()->index();
            $table->string('phone', 30)->nullable();
            $table->string('job_title')->nullable();
            $table->enum('position_level', ['entry', 'junior', 'mid', 'senior', 'manager', 'director', 'executive'])->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->string('national_id')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->decimal('salary_amount', 12, 2)->nullable();
            $table->enum('salary_type', ['hourly', 'daily', 'monthly'])->default('monthly');
            $table->string('photo_path')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['property_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
