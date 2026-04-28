<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('landlord')->table('tenants', function (Blueprint $table): void {
            $table->string('slug')->unique()->after('name');
            $table->enum('status', ['pending', 'active', 'trial', 'suspended', 'cancelled'])
                  ->default('pending')->after('database');
            $table->timestamp('trial_ends_at')->nullable()->after('status');
            $table->foreignId('plan_id')->nullable()->constrained('subscription_plans')->after('trial_ends_at');
            $table->string('contact_name')->nullable()->after('plan_id');
            $table->string('contact_email')->nullable()->after('contact_name');
            $table->string('contact_phone')->nullable()->after('contact_email');
            $table->timestamp('email_verified_at')->nullable()->after('contact_phone');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->table('tenants', function (Blueprint $table): void {
            $table->dropColumn([
                'slug', 'status', 'trial_ends_at', 'plan_id',
                'contact_name', 'contact_email', 'contact_phone',
                'email_verified_at', 'deleted_at',
            ]);
        });
    }
};
