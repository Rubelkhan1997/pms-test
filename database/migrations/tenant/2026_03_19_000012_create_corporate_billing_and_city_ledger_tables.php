<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->string('type')->default('corporate');
            $table->string('account_number')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->json('address')->nullable();
            $table->integer('payment_terms_days')->default(30);
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->boolean('allows_direct_billing')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['hotel_id', 'code']);
            $table->index(['hotel_id', 'is_active']);
        });

        Schema::create('company_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_billing_contact')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'is_primary']);
            $table->index(['company_id', 'is_billing_contact']);
        });

        Schema::create('city_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('ledger_code');
            $table->string('status')->default('active');
            $table->string('currency', 3)->default('USD');
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->date('statement_cycle_start')->nullable();
            $table->date('statement_cycle_end')->nullable();
            $table->timestamp('last_statement_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['hotel_id', 'ledger_code']);
            $table->unique(['company_id']);
            $table->index(['hotel_id', 'status']);
        });

        Schema::create('direct_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_ledger_id')->nullable()->constrained('city_ledgers')->nullOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('folio_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('billing_type')->default('all_charges');
            $table->string('status')->default('authorized');
            $table->decimal('authorized_amount', 12, 2)->nullable();
            $table->decimal('billed_amount', 12, 2)->default(0);
            $table->date('bill_to_date')->nullable();
            $table->date('due_date')->nullable();
            $table->text('instructions')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['reservation_id', 'folio_id']);
        });

        Schema::create('city_ledger_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_ledger_id')->constrained('city_ledgers')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('folio_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->date('transaction_date');
            $table->string('transaction_type');
            $table->string('reference_number')->nullable();
            $table->decimal('debit_amount', 12, 2)->default(0);
            $table->decimal('credit_amount', 12, 2)->default(0);
            $table->decimal('balance_after', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['city_ledger_id', 'transaction_date']);
            $table->index(['company_id', 'transaction_type']);
        });

        Schema::create('city_ledger_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_ledger_id')->constrained('city_ledgers')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->date('statement_date');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('total_debits', 12, 2)->default(0);
            $table->decimal('total_credits', 12, 2)->default(0);
            $table->decimal('closing_balance', 12, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['city_ledger_id', 'period_start', 'period_end']);
            $table->index(['company_id', 'statement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_ledger_statements');
        Schema::dropIfExists('city_ledger_transactions');
        Schema::dropIfExists('direct_billings');
        Schema::dropIfExists('city_ledgers');
        Schema::dropIfExists('company_contacts');
        Schema::dropIfExists('companies');
    }
};
