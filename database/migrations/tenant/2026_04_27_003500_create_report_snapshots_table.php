<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('report_type', 100)->index();
            $table->date('report_date')->index();
            $table->enum('status', ['pending', 'generating', 'ready', 'failed'])->default('pending')->index();
            $table->json('data')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['property_id', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_snapshots');
    }
};
