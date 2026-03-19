<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add hotel_id foreign key to users table.
 * This runs after hotels table is created.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Add foreign key constraint to hotel_id
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
        });
    }
};
