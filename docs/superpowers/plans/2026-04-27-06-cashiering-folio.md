# Cashiering & Folio Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the complete financial management layer — guest folio with line items, payment posting, charge routing, refunds, cashier shift management, invoices, and exchange rates.

**Architecture:** Each reservation has exactly one folio (created atomically in Phase 3). Folio items are immutable once posted — voiding creates a reversal entry, never deletes. City ledger routing moves line items to company/agent accounts. `balance_due` is recalculated by the service layer on every change.

**Tech Stack:** Laravel 13, MySQL 8, Pest PHP.

**Depends on:** Phase 3 (Reservation Engine — folios table seeded at reservation creation).

---

## File Map

| Action | File | Responsibility |
|---|---|---|
| Create | `database/migrations/tenant/2026_04_27_400000_create_folio_items_table.php` | All folio transactions |
| Create | `database/migrations/tenant/2026_04_27_400100_create_cashier_sessions_table.php` | Shift tracking |
| Create | `database/migrations/tenant/2026_04_27_400200_create_cashier_transactions_table.php` | Cashier action log |
| Create | `database/migrations/tenant/2026_04_27_400300_create_expense_vouchers_table.php` | Petty cash |
| Create | `database/migrations/tenant/2026_04_27_400400_create_invoices_table.php` | Guest/company/vendor invoices |
| Create | `database/migrations/tenant/2026_04_27_400500_create_exchange_rates_table.php` | Daily currency rates |
| Create | `app/Enums/FolioItemType.php` | room, payment, charge, tax, discount, refund, reversal |
| Create | `app/Enums/PaymentMethod.php` | cash, card, bank_transfer, advance |
| Create | `app/Modules/Cashiering/Models/FolioItem.php` | Immutable transaction line |
| Create | `app/Modules/Cashiering/Models/CashierSession.php` | Shift model |
| Create | `app/Modules/Cashiering/Models/CashierTransaction.php` | Cashier log model |
| Create | `app/Modules/Cashiering/Models/ExpenseVoucher.php` | Expense model |
| Create | `app/Modules/Cashiering/Models/Invoice.php` | Invoice model |
| Create | `app/Modules/Cashiering/Models/ExchangeRate.php` | Exchange rate model |
| Create | `app/Modules/Cashiering/Actions/PostPaymentAction.php` | Payment → folio item |
| Create | `app/Modules/Cashiering/Actions/PostChargeAction.php` | Charge → folio item |
| Create | `app/Modules/Cashiering/Actions/VoidFolioItemAction.php` | Reversal entry |
| Create | `app/Modules/Cashiering/Actions/PostRoomChargeAction.php` | Nightly room charge (used by Night Audit) |
| Create | `app/Modules/Cashiering/Actions/RefundAction.php` | Refund posting |
| Create | `app/Modules/Cashiering/Actions/RouteToCityLedgerAction.php` | Move item to company |
| Create | `app/Modules/Cashiering/Actions/OpenCashierSessionAction.php` | Open shift |
| Create | `app/Modules/Cashiering/Actions/CloseCashierSessionAction.php` | Close shift |
| Create | `app/Modules/Cashiering/Services/CashieringService.php` | Orchestrator |
| Create | `app/Modules/Cashiering/Services/FolioService.php` | Folio balance management |
| Create | `app/Modules/Cashiering/Controllers/Api/V1/FolioController.php` | Folio API |
| Create | `app/Modules/Cashiering/Controllers/Api/V1/PaymentController.php` | Payment API |
| Create | `app/Modules/Cashiering/Controllers/Api/V1/ChargeController.php` | Charge API |
| Create | `app/Modules/Cashiering/Controllers/Api/V1/CashierSessionController.php` | Shift API |
| Create | `app/Modules/Cashiering/Controllers/Api/V1/InvoiceController.php` | Invoice API |
| Create | `app/Modules/Cashiering/Controllers/Api/V1/ExchangeRateController.php` | Rates API |
| Create | `app/Modules/Cashiering/Resources/FolioResource.php` | |
| Create | `app/Modules/Cashiering/Resources/FolioItemResource.php` | |
| Create | `resources/js/Types/Cashiering/folio.ts` | TS types |
| Create | `resources/js/Stores/Cashiering/folioStore.ts` | Pinia store |
| Create | `resources/js/Composables/Cashiering/useFolio.ts` | Composable |
| Create | `resources/js/Utils/Mappers/folio.ts` | Mapper |
| Create | `resources/js/Pages/Cashiering/Center/Index.vue` | Cashiering dashboard |
| Create | `resources/js/Pages/Cashiering/Folio/Show.vue` | Open folio detail |
| Create | `resources/js/Pages/Cashiering/Payment/Create.vue` | Post payment form |
| Create | `resources/js/Pages/Cashiering/Charges/Create.vue` | Post charges form |
| Create | `tests/Feature/Cashiering/FolioPostingTest.php` | Payment/charge tests |
| Create | `tests/Feature/Cashiering/VoidRefundTest.php` | Void/refund tests |
| Create | `tests/Feature/Cashiering/CashierSessionTest.php` | Shift management tests |

---

## Task 1: Database Migrations

- [ ] **Step 1.1: Create FolioItemType and PaymentMethod enums**

Create `app/Enums/FolioItemType.php`:

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum FolioItemType: string
{
    case Room      = 'room';
    case Charge    = 'charge';
    case Tax       = 'tax';
    case Discount  = 'discount';
    case Payment   = 'payment';
    case Refund    = 'refund';
    case Reversal  = 'reversal';
    case POS       = 'pos';

    public function isCredit(): bool
    {
        return in_array($this, [self::Payment, self::Refund, self::Discount], true);
    }
}
```

Create `app/Enums/PaymentMethod.php`:

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash         = 'cash';
    case Card         = 'card';
    case BankTransfer = 'bank_transfer';
    case Advance      = 'advance';
    case Refund       = 'refund';
    case CityLedger   = 'city_ledger';
}
```

- [ ] **Step 1.2: Create folio_items migration**

Create `database/migrations/tenant/2026_04_27_400000_create_folio_items_table.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folio_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('folio_id')->constrained('folios')->cascadeOnDelete();
            $table->string('type');                              // FolioItemType enum
            $table->string('description');
            $table->decimal('amount', 10, 2);                   // positive = charge, negative = payment/credit
            $table->string('unit_type')->nullable();             // per_room, per_adult, per_child
            $table->integer('quantity')->default(1);
            $table->date('charge_date');
            $table->string('payment_method')->nullable();        // PaymentMethod enum
            $table->string('reference_type')->nullable();        // pos_order, manual, night_audit
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->boolean('is_voided')->default(false);
            $table->unsignedBigInteger('voided_by_item_id')->nullable(); // reversal item id
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->useCurrent();
            $table->index(['folio_id', 'type']);
            $table->index(['folio_id', 'charge_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folio_items');
    }
};
```

- [ ] **Step 1.3: Create cashier_sessions migration**

Create `database/migrations/tenant/2026_04_27_400100_create_cashier_sessions_table.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('property_id')->constrained('properties');
            $table->decimal('opening_balance', 10, 2)->default(0);
            $table->decimal('closing_balance', 10, 2)->nullable();
            $table->decimal('expected_closing', 10, 2)->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashier_sessions');
    }
};
```

- [ ] **Step 1.4: Create cashier_transactions migration**

Create `database/migrations/tenant/2026_04_27_400200_create_cashier_transactions_table.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cashier_session_id')->constrained('cashier_sessions');
            $table->foreignId('folio_item_id')->nullable()->constrained('folio_items');
            $table->string('type');                          // payment, refund, charge, void
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('cashier_session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashier_transactions');
    }
};
```

- [ ] **Step 1.5: Create expense_vouchers migration**

Create `database/migrations/tenant/2026_04_27_400300_create_expense_vouchers_table.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_vouchers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cashier_session_id')->nullable()->constrained('cashier_sessions');
            $table->string('category');                          // petty_cash, maintenance, admin
            $table->text('description');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->default('cash');
            $table->date('expense_date');
            $table->boolean('approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_vouchers');
    }
};
```

- [ ] **Step 1.6: Create invoices migration**

Create `database/migrations/tenant/2026_04_27_400400_create_invoices_table.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->enum('invoice_type', ['guest', 'company', 'vendor'])->default('guest');
            $table->foreignId('folio_id')->nullable()->constrained('folios')->nullOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained('guest_profiles')->nullOnDelete();
            $table->foreignId('company_id')->nullable();        // companies table (Phase 11)
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->char('currency', 3)->default('USD');
            $table->enum('status', ['draft', 'issued', 'paid', 'void'])->default('draft');
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['invoice_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
```

- [ ] **Step 1.7: Create exchange_rates migration**

Create `database/migrations/tenant/2026_04_27_400500_create_exchange_rates_table.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table): void {
            $table->id();
            $table->char('from_currency', 3);
            $table->char('to_currency', 3);
            $table->decimal('rate', 14, 6);
            $table->date('effective_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['from_currency', 'to_currency', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
```

- [ ] **Step 1.8: Run migrations**

```bash
php artisan migrate --path=database/migrations/tenant --database=mysql
```

Expected: 6 new tables created.

- [ ] **Step 1.9: Commit**

```bash
git add database/migrations/tenant/2026_04_27_400*.php app/Enums/FolioItemType.php app/Enums/PaymentMethod.php
git commit -m "feat: add cashiering migrations (folio_items, cashier_sessions, invoices, exchange_rates)"
```

---

## Task 2: Folio Models

- [ ] **Step 2.1: Create FolioItem model**

Create `app/Modules/Cashiering/Models/FolioItem.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Cashiering\Models;

use App\Enums\FolioItemType;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FolioItem extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'folio_id', 'type', 'description', 'amount', 'unit_type', 'quantity',
        'charge_date', 'payment_method', 'reference_type', 'reference_id',
        'is_voided', 'voided_by_item_id', 'posted_by',
    ];

    protected $casts = [
        'type'           => FolioItemType::class,
        'payment_method' => PaymentMethod::class,
        'amount'         => 'decimal:2',
        'is_voided'      => 'boolean',
        'charge_date'    => 'date',
    ];

    public function folio(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\FrontDesk\Models\Folio::class);
    }
}
```

- [ ] **Step 2.2: Create remaining Cashiering models**

Create `app/Modules/Cashiering/Models/CashierSession.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Cashiering\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashierSession extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'property_id', 'opening_balance', 'closing_balance',
        'expected_closing', 'status', 'opened_at', 'closed_at', 'notes',
    ];

    protected $casts = [
        'opening_balance'  => 'decimal:2',
        'closing_balance'  => 'decimal:2',
        'expected_closing' => 'decimal:2',
        'opened_at'        => 'datetime',
        'closed_at'        => 'datetime',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(CashierTransaction::class);
    }
}
```

Create `app/Modules/Cashiering/Models/CashierTransaction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Cashiering\Models;

use Illuminate\Database\Eloquent\Model;

class CashierTransaction extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['cashier_session_id', 'folio_item_id', 'type', 'amount', 'payment_method'];

    protected $casts = ['amount' => 'decimal:2'];
}
```

Create `app/Modules/Cashiering/Models/Invoice.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Cashiering\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'invoice_type', 'folio_id', 'guest_id', 'company_id',
        'subtotal', 'tax_amount', 'total', 'currency', 'status',
        'issue_date', 'due_date', 'paid_at', 'notes',
    ];

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'tax_amount'  => 'decimal:2',
        'total'       => 'decimal:2',
        'issue_date'  => 'date',
        'due_date'    => 'date',
        'paid_at'     => 'datetime',
    ];
}
```

Create `app/Modules/Cashiering/Models/ExchangeRate.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Cashiering\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = ['from_currency', 'to_currency', 'rate', 'effective_date', 'created_by'];

    protected $casts = ['rate' => 'decimal:6', 'effective_date' => 'date'];
}
```

- [ ] **Step 2.3: Commit**

```bash
git add app/Modules/Cashiering/Models/
git commit -m "feat: add FolioItem, CashierSession, Invoice, ExchangeRate models"
```

---

## Task 3: Core Folio Actions

**Files:**
- Create: `app/Modules/Cashiering/Actions/PostPaymentAction.php`
- Create: `app/Modules/Cashiering/Actions/PostChargeAction.php`
- Create: `app/Modules/Cashiering/Actions/VoidFolioItemAction.php`
- Create: `app/Modules/Cashiering/Actions/PostRoomChargeAction.php`
- Create: `app/Modules/Cashiering/Actions/RefundAction.php`
- Create: `app/Modules/Cashiering/Services/FolioService.php`
- Test: `tests/Feature/Cashiering/FolioPostingTest.php`

- [ ] **Step 3.1: Write failing tests**

Create `tests/Feature/Cashiering/FolioPostingTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\FolioItemType;
use App\Enums\FolioStatus;
use App\Modules\Cashiering\Actions\PostChargeAction;
use App\Modules\Cashiering\Actions\PostPaymentAction;
use App\Modules\Cashiering\Actions\VoidFolioItemAction;
use App\Modules\Cashiering\Models\FolioItem;
use App\Modules\FrontDesk\Models\Folio;

function makeFolio(): Folio
{
    return Folio::create([
        'reservation_id' => \Illuminate\Support\Str::uuid(),
        'guest_id'       => 1,
        'status'         => FolioStatus::Active,
        'currency'       => 'USD',
        'total_charges'  => 0,
        'total_payments' => 0,
        'balance_due'    => 0,
    ]);
}

it('posts a payment and decreases balance_due', function (): void {
    $folio = makeFolio();

    app(PostChargeAction::class)->execute($folio, 'Room charge', 200.00, FolioItemType::Room, today());
    app(PostPaymentAction::class)->execute($folio, 100.00, 'cash', 'Cash payment');

    $folio->refresh();
    expect((float) $folio->balance_due)->toBe(100.00)
        ->and((float) $folio->total_charges)->toBe(200.00)
        ->and((float) $folio->total_payments)->toBe(100.00);
});

it('posts a charge and increases balance_due', function (): void {
    $folio = makeFolio();
    app(PostChargeAction::class)->execute($folio, 'Laundry', 50.00, FolioItemType::Charge, today());

    $folio->refresh();
    expect((float) $folio->balance_due)->toBe(50.00)
        ->and((float) $folio->total_charges)->toBe(50.00);
});

it('voids a folio item by creating reversal', function (): void {
    $folio = makeFolio();
    $item  = app(PostChargeAction::class)->execute($folio, 'Minibar', 30.00, FolioItemType::Charge, today());

    app(VoidFolioItemAction::class)->execute($item, $folio);

    $folio->refresh();
    expect((float) $folio->balance_due)->toBe(0.0)
        ->and($item->fresh()->is_voided)->toBeTrue()
        ->and(FolioItem::where('type', FolioItemType::Reversal)->where('folio_id', $folio->id)->exists())->toBeTrue();
});
```

- [ ] **Step 3.2: Run test to verify it fails**

```bash
php artisan test tests/Feature/Cashiering/FolioPostingTest.php
```

Expected: FAIL.

- [ ] **Step 3.3: Create FolioService**

Create `app/Modules/Cashiering/Services/FolioService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Cashiering\Services;

use App\Modules\FrontDesk\Models\Folio;
use Illuminate\Support\Facades\DB;

class FolioService
{
    public function recalculate(Folio $folio): void
    {
        $totals = DB::table('folio_items')
            ->where('folio_id', $folio->id)
            ->where('is_voided', false)
            ->selectRaw('
                SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as total_charges,
                SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as total_payments
            ')
            ->first();

        $charges  = (float) ($totals->total_charges ?? 0);
        $payments = (float) ($totals->total_payments ?? 0);

        $folio->update([
            'total_charges'  => $charges,
            'total_payments' => $payments,
            'balance_due'    => $charges - $payments,
        ]);
    }
}
```

- [ ] **Step 3.4: Create PostChargeAction**

Create `app/Modules/Cashiering/Actions/PostChargeAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Cashiering\Actions;

use App\Enums\FolioItemType;
use App\Modules\Cashiering\Models\FolioItem;
use App\Modules\Cashiering\Services\FolioService;
use App\Modules\FrontDesk\Models\Folio;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PostChargeAction
{
    public function __construct(private readonly FolioService $folioService) {}

    public function execute(
        Folio        $folio,
        string       $description,
        float        $amount,
        FolioItemType $type,
        Carbon|\DateTimeInterface|string $chargeDate,
        string       $unitType = 'per_room',
        int          $quantity = 1,
        string       $referenceType = 'manual',
        ?int         $referenceId = null,
    ): FolioItem {
        $item = FolioItem::create([
            'folio_id'       => $folio->id,
            'type'           => $type,
            'description'    => $description,
            'amount'         => abs($amount),    // charges are always positive
            'unit_type'      => $unitType,
            'quantity'       => $quantity,
            'charge_date'    => $chargeDate,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'posted_by'      => Auth::id(),
        ]);

        $this->folioService->recalculate($folio);

        return $item;
    }
}
```

- [ ] **Step 3.5: Create PostPaymentAction**

Create `app/Modules/Cashiering/Actions/PostPaymentAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Cashiering\Actions;

use App\Enums\FolioItemType;
use App\Enums\FolioStatus;
use App\Modules\Cashiering\Models\FolioItem;
use App\Modules\Cashiering\Services\FolioService;
use App\Modules\FrontDesk\Models\Folio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostPaymentAction
{
    public function __construct(private readonly FolioService $folioService) {}

    public function execute(
        Folio  $folio,
        float  $amount,
        string $paymentMethod,
        string $description = 'Payment',
    ): FolioItem {
        return DB::transaction(function () use ($folio, $amount, $paymentMethod, $description): FolioItem {
            $item = FolioItem::create([
                'folio_id'       => $folio->id,
                'type'           => FolioItemType::Payment,
                'description'    => $description,
                'amount'         => -abs($amount),   // payments are negative (credits)
                'charge_date'    => today(),
                'payment_method' => $paymentMethod,
                'posted_by'      => Auth::id(),
            ]);

            $this->folioService->recalculate($folio);

            // Auto-settle if balance is 0
            $folio->refresh();
            if ((float) $folio->balance_due <= 0) {
                $folio->update(['status' => FolioStatus::Settled, 'settled_at' => now()]);
            }

            return $item;
        });
    }
}
```

- [ ] **Step 3.6: Create VoidFolioItemAction**

Create `app/Modules/Cashiering/Actions/VoidFolioItemAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Cashiering\Actions;

use App\Enums\FolioItemType;
use App\Modules\Cashiering\Models\FolioItem;
use App\Modules\Cashiering\Services\FolioService;
use App\Modules\FrontDesk\Models\Folio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoidFolioItemAction
{
    public function __construct(private readonly FolioService $folioService) {}

    public function execute(FolioItem $item, Folio $folio): FolioItem
    {
        if ($item->is_voided) {
            throw new \RuntimeException('Folio item is already voided.');
        }

        return DB::transaction(function () use ($item, $folio): FolioItem {
            // Create reversal entry (opposite amount)
            $reversal = FolioItem::create([
                'folio_id'           => $folio->id,
                'type'               => FolioItemType::Reversal,
                'description'        => "Void: {$item->description}",
                'amount'             => -$item->amount,
                'charge_date'        => today(),
                'reference_type'     => 'void',
                'reference_id'       => $item->id,
                'posted_by'          => Auth::id(),
            ]);

            // Mark original item as voided
            $item->update(['is_voided' => true, 'voided_by_item_id' => $reversal->id]);

            $this->folioService->recalculate($folio);

            return $reversal;
        });
    }
}
```

- [ ] **Step 3.7: Create PostRoomChargeAction (used by Night Audit)**

Create `app/Modules/Cashiering/Actions/PostRoomChargeAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Cashiering\Actions;

use App\Enums\FolioItemType;
use App\Modules\FrontDesk\Models\Folio;
use App\Modules\FrontDesk\Models\Reservation;
use Carbon\Carbon;

class PostRoomChargeAction
{
    public function __construct(private readonly PostChargeAction $postCharge) {}

    public function execute(Reservation $reservation, Carbon $businessDate): void
    {
        $folio = $reservation->folio;

        if (! $folio) {
            throw new \RuntimeException("No folio for reservation {$reservation->booking_ref}");
        }

        // Check if already posted for this date
        $alreadyPosted = \App\Modules\Cashiering\Models\FolioItem::where('folio_id', $folio->id)
            ->where('type', FolioItemType::Room)
            ->whereDate('charge_date', $businessDate)
            ->where('is_voided', false)
            ->exists();

        if ($alreadyPosted) {
            return;
        }

        $this->postCharge->execute(
            folio:        $folio,
            description:  "Room charge — {$businessDate->toDateString()}",
            amount:       (float) $reservation->base_rate,
            type:         FolioItemType::Room,
            chargeDate:   $businessDate,
            referenceType:'night_audit',
        );
    }
}
```

- [ ] **Step 3.8: Create RefundAction**

Create `app/Modules/Cashiering/Actions/RefundAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Cashiering\Actions;

use App\Enums\FolioItemType;
use App\Enums\PaymentMethod;
use App\Modules\Cashiering\Models\FolioItem;
use App\Modules\Cashiering\Services\FolioService;
use App\Modules\FrontDesk\Models\Folio;
use Illuminate\Support\Facades\Auth;

class RefundAction
{
    public function __construct(private readonly FolioService $folioService) {}

    public function execute(Folio $folio, float $amount, string $description = 'Refund'): FolioItem
    {
        $item = FolioItem::create([
            'folio_id'       => $folio->id,
            'type'           => FolioItemType::Refund,
            'description'    => $description,
            'amount'         => -abs($amount),   // refunds are credits
            'charge_date'    => today(),
            'payment_method' => PaymentMethod::Refund->value,
            'posted_by'      => Auth::id(),
        ]);

        $this->folioService->recalculate($folio);

        return $item;
    }
}
```

- [ ] **Step 3.9: Run tests to verify they pass**

```bash
php artisan test tests/Feature/Cashiering/FolioPostingTest.php
```

Expected: 3 tests PASS.

- [ ] **Step 3.10: Commit**

```bash
git add app/Modules/Cashiering/Actions/ app/Modules/Cashiering/Services/FolioService.php \
        tests/Feature/Cashiering/FolioPostingTest.php
git commit -m "feat: add PostPayment, PostCharge, VoidFolioItem, Refund, PostRoomCharge actions"
```

---

## Task 4: Cashier Session Actions

**Test:** `tests/Feature/Cashiering/CashierSessionTest.php`

- [ ] **Step 4.1: Write failing test**

Create `tests/Feature/Cashiering/CashierSessionTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Cashiering\Actions\CloseCashierSessionAction;
use App\Modules\Cashiering\Actions\OpenCashierSessionAction;
use App\Modules\Cashiering\Models\CashierSession;

it('opens a cashier session', function (): void {
    $user    = User::factory()->create();
    $session = app(OpenCashierSessionAction::class)->execute($user->id, propertyId: 1, openingBalance: 200.00);

    expect($session)->toBeInstanceOf(CashierSession::class)
        ->and($session->status)->toBe('open')
        ->and((float) $session->opening_balance)->toBe(200.00);
});

it('throws when opening second session while one is active', function (): void {
    $user = User::factory()->create();
    app(OpenCashierSessionAction::class)->execute($user->id, propertyId: 1, openingBalance: 0);

    expect(fn () => app(OpenCashierSessionAction::class)->execute($user->id, propertyId: 1, openingBalance: 0))
        ->toThrow(\RuntimeException::class);
});

it('closes a cashier session', function (): void {
    $user    = User::factory()->create();
    $session = app(OpenCashierSessionAction::class)->execute($user->id, propertyId: 1, openingBalance: 100.00);

    app(CloseCashierSessionAction::class)->execute($session, closingBalance: 350.00);

    expect($session->fresh()->status)->toBe('closed')
        ->and($session->fresh()->closed_at)->not->toBeNull();
});
```

- [ ] **Step 4.2: Run test to verify it fails**

```bash
php artisan test tests/Feature/Cashiering/CashierSessionTest.php
```

Expected: FAIL.

- [ ] **Step 4.3: Create OpenCashierSessionAction**

Create `app/Modules/Cashiering/Actions/OpenCashierSessionAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Cashiering\Actions;

use App\Modules\Cashiering\Models\CashierSession;

class OpenCashierSessionAction
{
    public function execute(int $userId, int $propertyId, float $openingBalance = 0): CashierSession
    {
        $existing = CashierSession::where('user_id', $userId)
            ->where('property_id', $propertyId)
            ->where('status', 'open')
            ->exists();

        if ($existing) {
            throw new \RuntimeException('A cashier session is already open for this user.');
        }

        return CashierSession::create([
            'user_id'         => $userId,
            'property_id'     => $propertyId,
            'opening_balance' => $openingBalance,
            'status'          => 'open',
            'opened_at'       => now(),
        ]);
    }
}
```

- [ ] **Step 4.4: Create CloseCashierSessionAction**

Create `app/Modules/Cashiering/Actions/CloseCashierSessionAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Cashiering\Actions;

use App\Modules\Cashiering\Models\CashierSession;
use Illuminate\Support\Facades\DB;

class CloseCashierSessionAction
{
    public function execute(CashierSession $session, float $closingBalance, string $notes = ''): void
    {
        if ($session->status === 'closed') {
            throw new \RuntimeException('Session is already closed.');
        }

        // Calculate expected closing: opening + all cash payments in this session
        $collected = DB::table('cashier_transactions')
            ->where('cashier_session_id', $session->id)
            ->where('type', 'payment')
            ->where('payment_method', 'cash')
            ->sum('amount');

        $session->update([
            'status'           => 'closed',
            'closing_balance'  => $closingBalance,
            'expected_closing' => (float) $session->opening_balance + abs((float) $collected),
            'closed_at'        => now(),
            'notes'            => $notes,
        ]);
    }
}
```

- [ ] **Step 4.5: Run tests to verify they pass**

```bash
php artisan test tests/Feature/Cashiering/CashierSessionTest.php
```

Expected: 3 tests PASS.

- [ ] **Step 4.6: Commit**

```bash
git add app/Modules/Cashiering/Actions/OpenCashierSessionAction.php \
        app/Modules/Cashiering/Actions/CloseCashierSessionAction.php \
        tests/Feature/Cashiering/CashierSessionTest.php
git commit -m "feat: add cashier session open/close actions"
```

---

## Task 5: CashieringService + API Controllers + Routes

- [ ] **Step 5.1: Create CashieringService**

Create `app/Modules/Cashiering/Services/CashieringService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Cashiering\Services;

use App\Enums\FolioItemType;
use App\Modules\Cashiering\Actions\PostChargeAction;
use App\Modules\Cashiering\Actions\PostPaymentAction;
use App\Modules\Cashiering\Actions\RefundAction;
use App\Modules\Cashiering\Actions\VoidFolioItemAction;
use App\Modules\Cashiering\Models\FolioItem;
use App\Modules\FrontDesk\Models\Folio;
use Illuminate\Support\Collection;

readonly class CashieringService
{
    public function __construct(
        private PostChargeAction  $postCharge,
        private PostPaymentAction $postPayment,
        private VoidFolioItemAction $voidItem,
        private RefundAction      $refund,
        private FolioService      $folioService,
    ) {}

    public function getOpenFolios(): Collection
    {
        return Folio::where('status', 'active')
            ->with(['reservation'])
            ->orderBy('balance_due', 'desc')
            ->get();
    }

    public function getFolioWithItems(int $folioId): Folio
    {
        return Folio::with(['items' => fn ($q) => $q->orderBy('posted_at')])->findOrFail($folioId);
    }

    public function postPayment(int $folioId, float $amount, string $method, string $desc): FolioItem
    {
        $folio = Folio::findOrFail($folioId);
        return $this->postPayment->execute($folio, $amount, $method, $desc);
    }

    public function postCharge(int $folioId, string $desc, float $amount, string $type, string $date): FolioItem
    {
        $folio = Folio::findOrFail($folioId);
        return $this->postCharge->execute($folio, $desc, $amount, FolioItemType::from($type), $date);
    }

    public function voidItem(int $folioId, int $itemId): void
    {
        $folio = Folio::findOrFail($folioId);
        $item  = FolioItem::where('folio_id', $folioId)->findOrFail($itemId);
        $this->voidItem->execute($item, $folio);
    }

    public function postRefund(int $folioId, float $amount, string $desc): FolioItem
    {
        $folio = Folio::findOrFail($folioId);
        return $this->refund->execute($folio, $amount, $desc);
    }
}
```

- [ ] **Step 5.2: Create FolioResource and FolioItemResource**

Create `app/Modules/Cashiering/Resources/FolioItemResource.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Cashiering\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FolioItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'type'           => $this->type->value,
            'description'    => $this->description,
            'amount'         => $this->amount,
            'unit_type'      => $this->unit_type,
            'quantity'       => $this->quantity,
            'charge_date'    => $this->charge_date->toDateString(),
            'payment_method' => $this->payment_method?->value,
            'is_voided'      => $this->is_voided,
            'posted_at'      => $this->posted_at,
        ];
    }
}
```

Create `app/Modules/Cashiering/Resources/FolioResource.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Cashiering\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FolioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'reservation_id' => $this->reservation_id,
            'guest_id'       => $this->guest_id,
            'status'         => $this->status->value,
            'total_charges'  => $this->total_charges,
            'total_payments' => $this->total_payments,
            'balance_due'    => $this->balance_due,
            'currency'       => $this->currency,
            'settled_at'     => $this->settled_at?->toIso8601String(),
            'items'          => FolioItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
```

- [ ] **Step 5.3: Create API controllers**

Create `app/Modules/Cashiering/Controllers/Api/V1/FolioController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Cashiering\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Cashiering\Resources\FolioResource;
use App\Modules\Cashiering\Services\CashieringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FolioController extends Controller
{
    public function __construct(private readonly CashieringService $service) {}

    public function openFolios(): JsonResponse
    {
        $folios = $this->service->getOpenFolios();
        return response()->json(['status' => 1, 'data' => FolioResource::collection($folios), 'message' => '']);
    }

    public function show(int $folioId): JsonResponse
    {
        $folio = $this->service->getFolioWithItems($folioId);
        return response()->json(['status' => 1, 'data' => new FolioResource($folio), 'message' => '']);
    }

    public function postPayment(Request $request, int $folioId): JsonResponse
    {
        $request->validate([
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,advance'],
            'description'    => ['nullable', 'string'],
        ]);

        $item = $this->service->postPayment($folioId, $request->float('amount'), $request->input('payment_method'), $request->input('description', 'Payment'));
        return response()->json(['status' => 1, 'data' => $item, 'message' => 'Payment posted.'], 201);
    }

    public function postCharge(Request $request, int $folioId): JsonResponse
    {
        $request->validate([
            'description' => ['required', 'string'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'type'        => ['required', 'in:charge,tax,discount,pos'],
            'charge_date' => ['required', 'date'],
        ]);

        $item = $this->service->postCharge($folioId, $request->input('description'), $request->float('amount'), $request->input('type'), $request->input('charge_date'));
        return response()->json(['status' => 1, 'data' => $item, 'message' => 'Charge posted.'], 201);
    }

    public function voidItem(int $folioId, int $itemId): JsonResponse
    {
        $this->service->voidItem($folioId, $itemId);
        return response()->json(['status' => 1, 'data' => null, 'message' => 'Voided.']);
    }

    public function refund(Request $request, int $folioId): JsonResponse
    {
        $request->validate(['amount' => ['required', 'numeric', 'min:0.01'], 'description' => ['nullable', 'string']]);
        $item = $this->service->postRefund($folioId, $request->float('amount'), $request->input('description', 'Refund'));
        return response()->json(['status' => 1, 'data' => $item, 'message' => 'Refund posted.'], 201);
    }
}
```

- [ ] **Step 5.4: Register routes**

In `routes/tenant-api.php`, add:

```php
Route::prefix('cashiering')->name('cashiering.')->group(function (): void {
    Route::get('/open-folios',                   [FolioController::class, 'openFolios'])->name('open-folios');
    Route::get('/folios/{folio}',                [FolioController::class, 'show'])->name('folios.show');
    Route::post('/folios/{folio}/payment',       [FolioController::class, 'postPayment'])->name('folios.payment');
    Route::post('/folios/{folio}/charge',        [FolioController::class, 'postCharge'])->name('folios.charge');
    Route::post('/folios/{folio}/refund',        [FolioController::class, 'refund'])->name('folios.refund');
    Route::delete('/folios/{folio}/items/{item}',[FolioController::class, 'voidItem'])->name('folios.void');

    Route::apiResource('invoices',       InvoiceController::class);
    Route::apiResource('exchange-rates', ExchangeRateController::class);
    Route::prefix('sessions')->name('sessions.')->group(function (): void {
        Route::post('/open',     [CashierSessionController::class, 'open'])->name('open');
        Route::post('/{session}/close', [CashierSessionController::class, 'close'])->name('close');
    });
});
```

- [ ] **Step 5.5: Commit**

```bash
git add app/Modules/Cashiering/ routes/tenant-api.php
git commit -m "feat: add CashieringService, folio API controllers, cashier session routes"
```

---

## Task 6: TypeScript Types, Store, Composable, Vue Pages

- [ ] **Step 6.1: Create TypeScript types**

Create `resources/js/Types/Cashiering/folio.ts`:

```typescript
export type FolioStatus = 'pending' | 'active' | 'settled' | 'voided'
export type FolioItemType = 'room' | 'charge' | 'tax' | 'discount' | 'payment' | 'refund' | 'reversal' | 'pos'
export type PaymentMethod = 'cash' | 'card' | 'bank_transfer' | 'advance'

export interface FolioItem {
  id: number
  type: FolioItemType
  description: string
  amount: string
  unit_type: string | null
  quantity: number
  charge_date: string
  payment_method: PaymentMethod | null
  is_voided: boolean
  posted_at: string
}

export interface Folio {
  id: number
  reservation_id: string
  guest_id: number
  status: FolioStatus
  total_charges: string
  total_payments: string
  balance_due: string
  currency: string
  settled_at: string | null
  items?: FolioItem[]
}
```

- [ ] **Step 6.2: Create Pinia store**

Create `resources/js/Stores/Cashiering/folioStore.ts`:

```typescript
import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiClient from '@/Services/apiClient'
import type { Folio } from '@/Types/Cashiering/folio'

export const useFolioStore = defineStore('folio', () => {
  const openFolios    = ref<Folio[]>([])
  const current       = ref<Folio | null>(null)
  const loading       = ref(false)
  const loadingList   = ref(false)
  const loadingDetail = ref(false)

  async function fetchOpenFolios(forceRefresh = false): Promise<void> {
    if (openFolios.value.length && !forceRefresh) return
    loadingList.value = true
    try {
      const res = await apiClient.get('/api/v1/cashiering/open-folios')
      openFolios.value = res.data.data
    } finally {
      loadingList.value = false
    }
  }

  async function fetchFolio(id: number): Promise<void> {
    loadingDetail.value = true
    try {
      const res = await apiClient.get(`/api/v1/cashiering/folios/${id}`)
      current.value = res.data.data
    } finally {
      loadingDetail.value = false
    }
  }

  async function postPayment(folioId: number, payload: Record<string, unknown>): Promise<void> {
    loading.value = true
    try {
      await apiClient.post(`/api/v1/cashiering/folios/${folioId}/payment`, payload)
      await fetchFolio(folioId)
    } finally {
      loading.value = false
    }
  }

  async function postCharge(folioId: number, payload: Record<string, unknown>): Promise<void> {
    loading.value = true
    try {
      await apiClient.post(`/api/v1/cashiering/folios/${folioId}/charge`, payload)
      await fetchFolio(folioId)
    } finally {
      loading.value = false
    }
  }

  return { openFolios, current, loading, loadingList, loadingDetail, fetchOpenFolios, fetchFolio, postPayment, postCharge }
})
```

- [ ] **Step 6.3: Create composable**

Create `resources/js/Composables/Cashiering/useFolio.ts`:

```typescript
import { storeToRefs } from 'pinia'
import { useFolioStore } from '@/Stores/Cashiering/folioStore'

export function useFolio() {
  const store = useFolioStore()
  const { openFolios, current, loading, loadingList, loadingDetail } = storeToRefs(store)
  return { openFolios, current, loading, loadingList, loadingDetail, ...store }
}
```

- [ ] **Step 6.4: Create Cashiering Center page**

Create `resources/js/Pages/Cashiering/Center/Index.vue`:

```vue
<script setup lang="ts">
import { onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { useFolio } from '@/Composables/Cashiering/useFolio'

const { openFolios, loadingList, fetchOpenFolios } = useFolio()

onMounted(() => fetchOpenFolios())
</script>

<template>
  <div class="p-6">
    <h1 class="text-xl font-semibold mb-4">Cashiering Center</h1>
    <div v-if="loadingList" class="text-gray-500 text-center py-8">Loading…</div>
    <table v-else class="w-full text-sm">
      <thead>
        <tr class="bg-gray-50 border-b">
          <th class="px-4 py-2 text-left">Folio #</th>
          <th class="px-4 py-2 text-left">Reservation</th>
          <th class="px-4 py-2 text-right">Charges</th>
          <th class="px-4 py-2 text-right">Payments</th>
          <th class="px-4 py-2 text-right">Balance Due</th>
          <th class="px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="f in openFolios" :key="f.id" class="border-b hover:bg-gray-50">
          <td class="px-4 py-2 font-mono">#{{ f.id }}</td>
          <td class="px-4 py-2 text-gray-600">{{ f.reservation_id.substring(0, 8) }}…</td>
          <td class="px-4 py-2 text-right">{{ f.currency }} {{ f.total_charges }}</td>
          <td class="px-4 py-2 text-right text-green-600">{{ f.currency }} {{ f.total_payments }}</td>
          <td class="px-4 py-2 text-right font-semibold" :class="parseFloat(f.balance_due) > 0 ? 'text-red-600' : 'text-green-600'">
            {{ f.currency }} {{ f.balance_due }}
          </td>
          <td class="px-4 py-2 flex gap-2 justify-center">
            <button class="btn-primary text-xs" @click="router.visit(`/cashiering/folios/${f.id}`)">View Folio</button>
          </td>
        </tr>
        <tr v-if="!openFolios.length">
          <td colspan="6" class="text-center py-8 text-gray-400">No open folios.</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
```

- [ ] **Step 6.5: Register web routes**

In `routes/tenant.php`:

```php
Route::prefix('cashiering')->name('cashiering.')->group(function (): void {
    Route::get('/',                    [\App\Modules\Cashiering\Controllers\Web\CashieringController::class, 'center'])->name('center');
    Route::get('/folios/{id}',         [\App\Modules\Cashiering\Controllers\Web\CashieringController::class, 'folio'])->name('folio');
    Route::get('/post-payment/{folio}',[\App\Modules\Cashiering\Controllers\Web\CashieringController::class, 'postPayment'])->name('post-payment');
    Route::get('/post-charges/{folio}',[\App\Modules\Cashiering\Controllers\Web\CashieringController::class, 'postCharges'])->name('post-charges');
    Route::get('/invoices',            [\App\Modules\Cashiering\Controllers\Web\CashieringController::class, 'invoices'])->name('invoices');
    Route::get('/exchange-rates',      [\App\Modules\Cashiering\Controllers\Web\CashieringController::class, 'exchangeRates'])->name('exchange-rates');
});
```

Create `app/Modules/Cashiering/Controllers/Web/CashieringController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Cashiering\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class CashieringController extends Controller
{
    public function center(): Response      { return Inertia::render('Cashiering/Center/Index'); }
    public function folio(int $id): Response { return Inertia::render('Cashiering/Folio/Show', ['folioId' => $id]); }
    public function postPayment(int $folio): Response { return Inertia::render('Cashiering/Payment/Create', ['folioId' => $folio]); }
    public function postCharges(int $folio): Response { return Inertia::render('Cashiering/Charges/Create', ['folioId' => $folio]); }
    public function invoices(): Response    { return Inertia::render('Cashiering/Invoices/Index'); }
    public function exchangeRates(): Response { return Inertia::render('Cashiering/ExchangeRates/Index'); }
}
```

- [ ] **Step 6.6: Run full test suite**

```bash
composer run test
```

Expected: all tests pass.

- [ ] **Step 6.7: Final commit**

```bash
git add resources/js/Types/Cashiering/ resources/js/Stores/Cashiering/ \
        resources/js/Composables/Cashiering/ resources/js/Pages/Cashiering/ \
        app/Modules/Cashiering/Controllers/Web/ routes/tenant.php
git commit -m "feat: add Cashiering Vue pages, store, composable, and web routes"
```

---

## Phase 5 Completion Checklist

- [ ] `POST /api/v1/cashiering/folios/{id}/payment` → decrements `balance_due`, auto-settles if 0
- [ ] `POST /api/v1/cashiering/folios/{id}/charge` → increments `balance_due`
- [ ] `DELETE /api/v1/cashiering/folios/{id}/items/{item}` → creates reversal, marks original voided
- [ ] `POST /api/v1/cashiering/folios/{id}/refund` → credits folio
- [ ] Folio items are never deleted — only reversed
- [ ] Cashier session: open → only one active session per user; close → records closing balance
- [ ] `PostRoomChargeAction` is idempotent — posting same date twice is a no-op
- [ ] `composer run test` → all PASS
- [ ] `./vendor/bin/pint` → no violations
