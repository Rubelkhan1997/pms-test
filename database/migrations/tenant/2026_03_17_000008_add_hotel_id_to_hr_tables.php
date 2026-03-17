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
        Schema::table('attendances', function (Blueprint $table): void {
            $table->foreignId('hotel_id')->after('id')->constrained()->cascadeOnDelete();
            
            $table->index(['hotel_id', 'attendance_date']);
        });
        
        Schema::table('payrolls', function (Blueprint $table): void {
            $table->foreignId('hotel_id')->after('id')->constrained()->cascadeOnDelete();
            $table->timestamp('paid_at')->nullable()->after('status');
            $table->json('meta')->nullable()->after('paid_at');
            
            $table->index(['hotel_id', 'status']);
        });
        
        Schema::table('shift_schedules', function (Blueprint $table): void {
            $table->foreignId('hotel_id')->after('id')->constrained()->cascadeOnDelete();
            
            $table->index(['hotel_id', 'shift_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropForeign(['hotel_id']);
            $table->dropIndex(['hotel_id', 'attendance_date']);
            $table->dropColumn('hotel_id');
        });
        
        Schema::table('payrolls', function (Blueprint $table): void {
            $table->dropForeign(['hotel_id']);
            $table->dropIndex(['hotel_id', 'status']);
            $table->dropColumn(['hotel_id', 'paid_at', 'meta']);
        });
        
        Schema::table('shift_schedules', function (Blueprint $table): void {
            $table->dropForeign(['hotel_id']);
            $table->dropIndex(['hotel_id', 'shift_date']);
            $table->dropColumn('hotel_id');
        });
    }
};
