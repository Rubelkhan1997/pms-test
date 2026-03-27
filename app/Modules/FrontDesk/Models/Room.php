<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use App\Enums\RoomStatus;
use App\Modules\FrontDesk\Models\Hotel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rooms';

    protected $fillable = [
        'hotel_id',
        'number',
        'floor',
        'type',
        'status',
        'base_rate',
    ];

    /**
     * Get cast definitions.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RoomStatus::class,
            'base_rate' => 'decimal:2',
        ];
    }

    /**
     * Get the owning hotel.
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get reservations for the room.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Scope for available rooms.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', RoomStatus::Available->value);
    }
}


