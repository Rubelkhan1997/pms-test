<?php

declare(strict_types=1);

use App\Enums\HousekeepingStatus;
use App\Enums\MaintenanceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('housekeeping_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->unique();
            $table->enum('status', array_map(static fn (HousekeepingStatus $item): string => $item->value, HousekeepingStatus::cases()))
                ->default(HousekeepingStatus::Pending->value)
                ->index();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['hotel_id', 'room_id']);
        });

        Schema::create('maintenance_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', array_map(static fn (MaintenanceStatus $item): string => $item->value, MaintenanceStatus::cases()))
                ->default(MaintenanceStatus::Open->value)
                ->index();
            $table->timestamp('reported_at')->index();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['hotel_id', 'room_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
        Schema::dropIfExists('housekeeping_tasks');
    }
};
