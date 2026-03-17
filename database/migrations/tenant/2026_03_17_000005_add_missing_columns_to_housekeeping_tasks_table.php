<?php

declare(strict_types=1);

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
        Schema::table('housekeeping_tasks', function (Blueprint $table): void {
            $table->string('task_type', 50)->default('cleaning')->after('room_id')->index();
            $table->string('priority', 20)->default('normal')->after('status')->index();
            $table->text('description')->nullable()->after('reference');
            $table->foreignId('assigned_to')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable()->after('scheduled_at');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            
            $table->index(['hotel_id', 'task_type']);
            $table->index(['hotel_id', 'assigned_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('housekeeping_tasks', function (Blueprint $table): void {
            $table->dropForeign(['assigned_to']);
            $table->dropIndex(['hotel_id', 'task_type']);
            $table->dropIndex(['hotel_id', 'assigned_to']);
            $table->dropColumn([
                'task_type',
                'priority',
                'description',
                'assigned_to',
                'started_at',
                'completed_at',
            ]);
        });
    }
};
