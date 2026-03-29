<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Guest Preference Model
 */
class GuestPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_profile_id',
        'category',
        'preference',
        'value',
        'priority',
        'is_fulfilled',
        'notes',
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_fulfilled' => 'boolean',
    ];

    public function guestProfile(): BelongsTo
    {
        return $this->belongsTo(GuestProfile::class);
    }

    public static function getForGuest(int $guestProfileId): array
    {
        return static::where('guest_profile_id', $guestProfileId)
            ->orderByDesc('priority')
            ->get()
            ->toArray();
    }

    public static function getRoomPreferences(int $guestProfileId): array
    {
        return static::where('guest_profile_id', $guestProfileId)
            ->where('category', 'room')
            ->orderByDesc('priority')
            ->get()
            ->toArray();
    }
}
