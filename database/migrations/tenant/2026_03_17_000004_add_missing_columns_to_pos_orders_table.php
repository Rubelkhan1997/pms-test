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
        Schema::table('pos_orders', function (Blueprint $table): void {
            $table->foreignId('reservation_id')->nullable()->after('hotel_id')->constrained()->nullOnDelete();
            $table->decimal('total_amount', 12, 2)->default(0)->after('status');
            $table->string('guest_name')->nullable()->after('total_amount');
            $table->string('room_number', 20)->nullable()->after('guest_name');
            $table->timestamp('served_at')->nullable()->after('scheduled_at');
            $table->timestamp('settled_at')->nullable()->after('served_at');
            $table->json('items')->nullable()->after('meta');
            
            $table->index(['hotel_id', 'status']);
            $table->index(['hotel_id', 'reservation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table): void {
            $table->dropForeign(['reservation_id']);
            $table->dropIndex(['hotel_id', 'status']);
            $table->dropIndex(['hotel_id', 'reservation_id']);
            $table->dropColumn([
                'reservation_id',
                'total_amount',
                'guest_name',
                'room_number',
                'served_at',
                'settled_at',
                'items',
            ]);
        });
    }
};
