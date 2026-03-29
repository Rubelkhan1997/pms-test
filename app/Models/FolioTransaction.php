<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Folio Transaction Model
 */
class FolioTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'folio_id',
        'reservation_id',
        'transaction_date',
        'transaction_time',
        'transaction_type',
        'description',
        'revenue_category_id',
        'amount',
        'balance',
        'room_id',
        'reference_type',
        'reference_id',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'transaction_time' => 'datetime:H:i',
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function revenueCategory(): BelongsTo
    {
        return $this->belongsTo(RevenueCategory::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
