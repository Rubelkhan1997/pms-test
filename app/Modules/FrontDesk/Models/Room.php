<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use App\Enums\RoomStatus;
use App\Models\Hotel;
use App\Traits\HasHotel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, HasHotel, SoftDeletes;

    protected $table = 'rooms';

    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'number',
        'floor',
        'type',
        'view_type',
        'smoking',
        'status',
        'base_rate',
        'notes',
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
            'smoking' => 'boolean',
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

    /**
     * Get current reservation (if occupied).
     */
    public function currentReservation(): ?Reservation
    {
        return $this->reservations()
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->latest('check_in_date')
            ->first();
    }
}


