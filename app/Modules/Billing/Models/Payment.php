<?php

declare(strict_types=1);

namespace App\Modules\Billing\Models;

use App\Models\User;
use App\Modules\FrontDesk\Models\Property;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\System\Models\Currency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'property_id', 'folio_id', 'reservation_id', 'currency_id', 'created_by',
        'method', 'amount', 'exchange_rate',
        'reference', 'card_last_four', 'card_brand',
        'status', 'paid_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:2',
            'exchange_rate' => 'decimal:6',
            'paid_at'       => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }
}
