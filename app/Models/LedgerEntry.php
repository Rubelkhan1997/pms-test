<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ledger Entry Model - Double-entry bookkeeping
 */
class LedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_number',
        'folio_transaction_id',
        'entry_date',
        'description',
        'entry_type',
        'revenue_category_id',
        'hotel_id',
        'reservation_id',
        'user_id',
        'debit_amount',
        'credit_amount',
        'balance',
        'debit_account',
        'credit_account',
        'is_posted',
        'posted_at',
        'notes',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_posted' => 'boolean',
        'posted_at' => 'datetime',
    ];

    public function folioTransaction(): BelongsTo
    {
        return $this->belongsTo(FolioTransaction::class);
    }

    public function revenueCategory(): BelongsTo
    {
        return $this->belongsTo(RevenueCategory::class);
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function createEntry(
        string $description,
        string $debitAccount,
        string $creditAccount,
        float $amount,
        int $hotelId,
        ?int $reservationId = null,
        ?int $userId = null
    ): self {
        $batchNumber = 'BATCH-' . now()->format('Ymd') . '-' . str_pad((string) static::count() + 1, 6, '0', STR_PAD_LEFT);

        return static::create([
            'batch_number' => $batchNumber,
            'entry_date' => now(),
            'description' => $description,
            'entry_type' => 'charge',
            'hotel_id' => $hotelId,
            'reservation_id' => $reservationId,
            'user_id' => $userId,
            'debit_amount' => $amount,
            'credit_amount' => 0,
            'balance' => $amount,
            'debit_account' => $debitAccount,
            'credit_account' => $creditAccount,
            'is_posted' => true,
            'posted_at' => now(),
        ]);
    }
}
