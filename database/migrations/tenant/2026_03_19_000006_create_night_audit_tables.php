<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Business Dates (CRITICAL - Hotel operates on business date, not system date)
        Schema::create('business_dates', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->boolean('is_open')->default(true);
            $table->boolean('is_current')->default(false);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['is_current', 'is_open']);
        });

        // Night Audits (Master Record)
        Schema::create('night_audits', function (Blueprint $table) {
            $table->id();
            $table->date('business_date')->unique();
            $table->string('status')->default('pending'); // pending, running, completed, failed
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('run_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('duration_seconds')->nullable();
            $table->text('notes')->nullable();
            $table->json('summary')->nullable(); // Quick stats
            $table->timestamps();

            $table->index(['status', 'business_date']);
        });

        // Night Audit Logs (Step-by-step execution log)
        Schema::create('night_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('night_audit_id')->constrained()->cascadeOnDelete();
            $table->string('step'); // validate, post_charges, update_revenue, rollover, generate_reports
            $table->string('status')->default('pending'); // success, failed, skipped
            $table->text('message')->nullable();
            $table->json('data')->nullable(); // Step-specific data
            $table->integer('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['night_audit_id', 'step']);
        });

        // Daily Statistics
        Schema::create('daily_statistics', function (Blueprint $table) {
            $table->id();
            $table->date('business_date')->unique();

            // Room counts
            $table->integer('total_rooms')->default(0);
            $table->integer('occupied_rooms')->default(0);
            $table->integer('available_rooms')->default(0);
            $table->integer('vacant_clean_rooms')->default(0);
            $table->integer('vacant_dirty_rooms')->default(0);
            $table->integer('out_of_order_rooms')->default(0);

            // Guest movements
            $table->integer('check_ins')->default(0);
            $table->integer('check_outs')->default(0);
            $table->integer('stayovers')->default(0);
            $table->integer('no_shows')->default(0);
            $table->integer('cancellations')->default(0);

            // Revenue totals
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('room_revenue', 12, 2)->default(0);
            $table->decimal('tax_revenue', 12, 2)->default(0);
            $table->decimal('service_revenue', 12, 2)->default(0);
            $table->decimal('other_revenue', 12, 2)->default(0);

            // Payments
            $table->decimal('total_payments', 12, 2)->default(0);
            $table->decimal('cash_payments', 12, 2)->default(0);
            $table->decimal('card_payments', 12, 2)->default(0);

            $table->timestamps();

            $table->index('business_date');
        });

        // Revenue Reports
        Schema::create('revenue_reports', function (Blueprint $table) {
            $table->id();
            $table->date('business_date');
            $table->string('report_type')->default('daily'); // daily, monthly, yearly
            $table->decimal('room_revenue', 12, 2)->default(0);
            $table->decimal('room_charge_revenue', 12, 2)->default(0);
            $table->decimal('service_revenue', 12, 2)->default(0);
            $table->decimal('tax_revenue', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('refund_total', 12, 2)->default(0);
            $table->decimal('net_revenue', 12, 2)->default(0);
            $table->json('breakdown')->nullable(); // By category, room type, etc.
            $table->timestamps();

            $table->index(['business_date', 'report_type']);
        });

        // Occupancy Reports
        Schema::create('occupancy_reports', function (Blueprint $table) {
            $table->id();
            $table->date('business_date');
            $table->string('report_type')->default('daily');

            // Occupancy metrics
            $table->decimal('occupancy_rate', 5, 2)->default(0); // Percentage
            $table->decimal('adr', 10, 2)->default(0); // Average Daily Rate
            $table->decimal('revpar', 10, 2)->default(0); // Revenue Per Available Room
            $table->decimal('trevpar', 10, 2)->default(0); // Total Revenue Per Available Room

            // Booking metrics
            $table->integer('rooms_sold')->default(0);
            $table->integer('rooms_available')->default(0);
            $table->decimal('average_length_of_stay', 4, 2)->default(0);

            // Market segmentation
            $table->json('by_room_type')->nullable(); // Occupancy by room type
            $table->json('by_channel')->nullable(); // Occupancy by channel

            $table->timestamps();

            $table->index(['business_date', 'report_type']);
        });

        // Audit Exceptions (Issues requiring attention)
        Schema::create('audit_exceptions', function (Blueprint $table) {
            $table->id();
            $table->date('business_date');
            $table->string('type'); // payment_mismatch, open_folio, rate_discrepancy, inventory_mismatch
            $table->string('severity')->default('medium'); // low, medium, high, critical
            $table->text('description');
            $table->json('details')->nullable();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('folio_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index(['business_date', 'resolved', 'severity']);
        });

        // Audit Snapshots (Full system state for reconciliation)
        Schema::create('audit_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('night_audit_id')->constrained()->cascadeOnDelete();
            $table->string('snapshot_type'); // reservations, folios, inventory, rates
            $table->json('data'); // Complete snapshot data
            $table->integer('record_count')->default(0);
            $table->timestamps();

            $table->index(['night_audit_id', 'snapshot_type']);
        });

        // Auto Audit Scheduler (For automated nightly runs)
        Schema::create('auto_audit_schedules', function (Blueprint $table) {
            $table->id();
            $table->time('scheduled_time')->default('02:00:00');
            $table->boolean('is_enabled')->default(true);
            $table->json('days_of_week')->nullable(); // [0,1,2,3,4,5,6] for all days
            $table->boolean('auto_post_charges')->default(true);
            $table->boolean('auto_generate_reports')->default(true);
            $table->boolean('send_email_summary')->default(false);
            $table->string('email_recipients')->nullable();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('auto_audit_schedules');
        Schema::dropIfExists('audit_snapshots');
        Schema::dropIfExists('audit_exceptions');
        Schema::dropIfExists('occupancy_reports');
        Schema::dropIfExists('revenue_reports');
        Schema::dropIfExists('daily_statistics');
        Schema::dropIfExists('night_audit_logs');
        Schema::dropIfExists('night_audits');
        Schema::dropIfExists('business_dates');
    }
};
