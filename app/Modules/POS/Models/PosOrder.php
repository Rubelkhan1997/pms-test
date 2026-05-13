<?php

declare(strict_types=1);

namespace App\Modules\POS\Models;

use App\Models\User;
use App\Modules\Billing\Models\Folio;
use App\Modules\FrontDesk\Models\Property;
use App\Modules\FrontDesk\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosOrder extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pos_orders';

    protected $fillable = [
        'property_id', 'outlet_id', 'reservation_id', 'folio_id', 'created_by',
        'reference', 'order_type', 'table_identifier', 'status',
        'subtotal', 'discount_amount', 'tax_amount', 'total_amount', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'        => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'total_amount'    => 'decimal:2',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class, 'outlet_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosOrderItem::class, 'order_id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['ordered', 'preparing', 'ready', 'served']);
    }
}
