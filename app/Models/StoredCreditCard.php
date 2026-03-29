<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stored Credit Card Model - PCI Compliant
 */
class StoredCreditCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_profile_id',
        'card_token',
        'card_brand',
        'card_last_four',
        'expiry_month',
        'expiry_year',
        'cardholder_name',
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_zip',
        'billing_country',
        'is_default',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'expiry_month' => 'integer',
        'expiry_year' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function guestProfile(): BelongsTo
    {
        return $this->belongsTo(GuestProfile::class);
    }

    public function getExpiryAttribute(): string
    {
        return sprintf('%02d/%d', $this->expiry_month, $this->expiry_year);
    }

    public function getMaskedNumberAttribute(): string
    {
        return "**** **** **** {$this->card_last_four}";
    }

    public function isExpired(): bool
    {
        $expiryDate = now()->setMonth($this->expiry_month)->setYear($this->expiry_year);
        return $expiryDate->isPast();
    }

    public static function getDefaultForGuest(int $guestProfileId): ?self
    {
        return static::where('guest_profile_id', $guestProfileId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    public static function getActiveCardsForGuest(int $guestProfileId): array
    {
        return static::where('guest_profile_id', $guestProfileId)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderByDesc('last_used_at')
            ->get()
            ->toArray();
    }
}
