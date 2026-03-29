<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Payment Method Model
 */
class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'allows_room_charge',
        'allows_online_payment',
        'processor',
        'processing_fee_percent',
        'processing_fee_fixed',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'allows_room_charge' => 'boolean',
        'allows_online_payment' => 'boolean',
        'processing_fee_percent' => 'decimal:2',
        'processing_fee_fixed' => 'decimal:2',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public static function getActive(): array
    {
        return static::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public static function getForRoomCharge(): array
    {
        return static::where('allows_room_charge', true)
            ->where('is_active', true)
            ->get()
            ->toArray();
    }

    public static function getForOnlinePayment(): array
    {
        return static::where('allows_online_payment', true)
            ->where('is_active', true)
            ->get()
            ->toArray();
    }
}
