<?php

declare(strict_types=1);

namespace App\Modules\Pos\Models;

use App\Enums\POSOrderStatus;
use App\Models\Hotel;
use App\Models\User;
use App\Modules\FrontDesk\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pos_orders';

    protected $fillable = [
        'hotel_id',
        'reservation_id',
        'created_by',
        'reference',
        'outlet',
        'status',
        'total_amount',
        'guest_name',
        'room_number',
        'scheduled_at',
        'served_at',
        'settled_at',
        'items',
        'meta',
    ];

    /**
     * Get cast definitions.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => POSOrderStatus::class,
            'scheduled_at' => 'datetime',
            'served_at' => 'datetime',
            'settled_at' => 'datetime',
            'total_amount' => 'decimal:2',
            'items' => 'array',
            'meta' => 'array',
        ];
    }

    /**
     * Get hotel.
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get reservation.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get creator.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope open orders.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            POSOrderStatus::Draft->value,
            POSOrderStatus::Submitted->value,
        ]);
    }

    /**
     * Scope by outlet.
     */
    public function scopeByOutlet(Builder $query, string $outlet): Builder
    {
        return $query->where('outlet', $outlet);
    }

    /**
     * Scope today's orders.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('scheduled_at', today());
    }
}
