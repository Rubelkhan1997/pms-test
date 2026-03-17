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
        Schema::table('mobile_tasks', function (Blueprint $table): void {
            $table->string('task_type', 50)->default('inspection')->after('reference')->index();
            $table->foreignId('assigned_to')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->text('description')->nullable()->after('task_type');
            $table->timestamp('completed_at')->nullable()->after('scheduled_at');
            
            $table->index(['hotel_id', 'task_type']);
            $table->index(['hotel_id', 'assigned_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobile_tasks', function (Blueprint $table): void {
            $table->dropForeign(['assigned_to']);
            $table->dropIndex(['hotel_id', 'task_type']);
            $table->dropIndex(['hotel_id', 'assigned_to']);
            $table->dropColumn([
                'task_type',
                'assigned_to',
                'description',
                'completed_at',
            ]);
        });
    }
};
