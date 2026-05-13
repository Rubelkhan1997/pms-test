<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ota_syncs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained('channels')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->unique();
            $table->enum('type', ['availability', 'rates', 'reservations', 'all'])->index();
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'partial'])->default('pending')->index();
            $table->date('sync_from')->nullable();
            $table->date('sync_to')->nullable();
            $table->unsignedInteger('records_sent')->default(0);
            $table->unsignedInteger('records_updated')->default(0);
            $table->unsignedInteger('errors_count')->default(0);
            $table->json('error_log')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['property_id', 'channel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ota_syncs');
    }
};
