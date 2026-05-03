<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['housekeeping', 'maintenance', 'concierge', 'delivery', 'request', 'other'])->default('request')->index();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->index();
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'])->default('pending')->index();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['property_id', 'status']);
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_tasks');
    }
};
