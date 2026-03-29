<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Reservation Guest Model
 */
class ReservationGuest extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'guest_profile_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'type',
        'date_of_birth',
        'gender',
        'nationality',
        'passport_number',
        'passport_expiry',
        'passport_country',
        'id_type',
        'id_number',
        'id_expiry',
        'is_primary',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'passport_expiry' => 'date',
        'id_expiry' => 'date',
        'is_primary' => 'boolean',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function guestProfile(): BelongsTo
    {
        return $this->belongsTo(GuestProfile::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public static function getPrimaryGuest(int $reservationId): ?self
    {
        return static::where('reservation_id', $reservationId)
            ->where('is_primary', true)
            ->first();
    }

    public static function getAllGuests(int $reservationId): array
    {
        return static::where('reservation_id', $reservationId)
            ->orderBy('is_primary', 'desc')
            ->orderBy('type')
            ->get()
            ->toArray();
    }
}
