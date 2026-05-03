<?php

declare(strict_types=1);

namespace App\Modules\Billing\Models;

use App\Modules\FrontDesk\Models\Property;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\Guest\Models\Guest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folio extends Model
{
    use HasFactory;

    protected $table = 'folios';

    protected $fillable = [
        'property_id', 'reservation_id', 'guest_id',
        'folio_number', 'type', 'status',
        'subtotal', 'tax_amount', 'total_amount', 'paid_amount', 'balance',
        'invoice_number', 'invoiced_at', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'     => 'decimal:2',
            'tax_amount'   => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount'  => 'decimal:2',
            'balance'      => 'decimal:2',
            'invoiced_at'  => 'datetime',
            'closed_at'    => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FolioTransaction::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }
}
