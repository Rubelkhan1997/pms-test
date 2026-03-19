<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Housekeeping Statuses (Normalized statuses)
        Schema::create('housekeeping_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // clean, dirty, inspected, out_of_order, out_of_service
            $table->string('code')->unique();
            $table->string('color')->nullable(); // for UI display
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Update rooms table - add housekeeping status FK
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('housekeeping_status_id')->nullable()->after('status')
                ->constrained('housekeeping_statuses')->nullOnDelete();
            $table->string('cleaning_priority')->default('normal')->after('housekeeping_status_id'); // low, normal, high, vip
        });

        // Housekeeping Staff
        Schema::create('housekeeping_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('role')->default('cleaner'); // cleaner, supervisor, manager
            $table->string('phone')->nullable();
            $table->string('employee_id')->nullable();
            $table->integer('rooms_per_day_target')->default(15);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['role', 'is_active']);
        });

        // Housekeeping Tasks (Work Orders)
        Schema::create('housekeeping_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('task_type')->default('cleaning'); // cleaning, deep_cleaning, inspection, maintenance_support, turndown
            $table->string('priority')->default('medium'); // low, medium, high, vip
            $table->string('status')->default('pending'); // pending, assigned, in_progress, completed, cancelled
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('estimated_duration')->nullable(); // minutes
            $table->integer('actual_duration')->nullable(); // minutes
            $table->text('instructions')->nullable();
            $table->text('notes')->nullable();
            $table->json('checklist')->nullable(); // cleaning checklist items
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
            $table->index(['room_id', 'status']);
        });

        // Housekeeping Task Assignments
        Schema::create('housekeeping_task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('housekeeping_tasks')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('housekeeping_staff')->cascadeOnDelete();
            $table->timestamp('assigned_at');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['task_id', 'staff_id']);
        });

        // Housekeeping Logs (Audit Trail)
        Schema::create('housekeeping_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('old_status_id')->nullable()->constrained('housekeeping_statuses')->nullOnDelete();
            $table->foreignId('new_status_id')->nullable()->constrained('housekeeping_statuses')->nullOnDelete();
            $table->timestamp('changed_at');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->string('source')->default('manual'); // manual, auto_checkout, auto_inspection
            $table->timestamps();

            $table->index(['room_id', 'changed_at']);
        });

        // Room Inspections (Supervisor Validation)
        Schema::create('room_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('housekeeping_tasks')->nullOnDelete();
            $table->string('result')->default('passed'); // passed, failed
            $table->integer('score')->nullable(); // 1-100
            $table->text('remarks')->nullable();
            $table->json('checklist_results')->nullable(); // inspection checklist
            $table->timestamp('inspected_at');
            $table->timestamps();

            $table->index(['room_id', 'inspected_at']);
        });

        // Lost and Found
        Schema::create('lost_and_found', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // electronics, clothing, documents, jewelry, other
            $table->date('found_date');
            $table->foreignId('found_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('location_found')->nullable();
            $table->string('status')->default('stored'); // stored, claimed, discarded, donated
            $table->date('claimed_at')->nullable();
            $table->foreignId('claimed_by')->nullable()->constrained('guests')->nullOnDelete();
            $table->text('disposition_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'found_date']);
        });

        // Linen Inventory (Advanced)
        Schema::create('linen_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // towel, bath_sheet, bedsheet, pillowcase, duvet_cover
            $table->string('category')->default('linen'); // linen, amenity, equipment
            $table->string('size')->nullable(); // single, double, king
            $table->string('color')->nullable();
            $table->integer('reorder_level')->default(20);
            $table->integer('par_level')->default(100); // ideal quantity
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('linen_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('linen_type_id')->constrained()->cascadeOnDelete();
            $table->integer('total_quantity')->default(0);
            $table->integer('available_quantity')->default(0);
            $table->integer('in_launder_quantity')->default(0);
            $table->integer('damaged_quantity')->default(0);
            $table->string('location')->default('main_storage'); // main_storage, floor_1, floor_2, etc.
            $table->date('last_counted_at')->nullable();
            $table->timestamps();

            $table->unique(['linen_type_id', 'location']);
        });

        Schema::create('linen_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('linen_type_id')->constrained()->cascadeOnDelete();
            $table->string('movement_type'); // add, remove, transfer, damage, launder
            $table->integer('quantity');
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['linen_type_id', 'created_at']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('linen_movements');
        Schema::dropIfExists('linen_inventory');
        Schema::dropIfExists('linen_types');
        Schema::dropIfExists('lost_and_found');
        Schema::dropIfExists('room_inspections');
        Schema::dropIfExists('housekeeping_logs');
        Schema::dropIfExists('housekeeping_task_assignments');
        Schema::dropIfExists('housekeeping_tasks');
        Schema::dropIfExists('housekeeping_staff');
        
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['housekeeping_status_id']);
            $table->dropColumn(['housekeeping_status_id', 'cleaning_priority']);
        });
        
        Schema::dropIfExists('housekeeping_statuses');
    }
};
