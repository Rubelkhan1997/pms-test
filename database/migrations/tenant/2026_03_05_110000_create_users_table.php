<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create users table for tenant database.
 * This MUST run first before any migrations that reference users.
 *
 * Note: Additional user fields are added in follow-up migrations, including
 * the account activation flag.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Users (tenant-specific users)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['hotel_id', 'email']);
        });

        // Password reset tokens (tenant-specific)
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('users');
    }
};
