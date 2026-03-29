<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Business Date Model - Night Audit
 */
class BusinessDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'actual_date',
        'business_date',
        'status',
        'is_current',
        'opened_at',
        'closed_at',
        'opened_by',
        'closed_by',
        'audited_by',
        'audited_at',
        'notes',
    ];

    protected $casts = [
        'actual_date' => 'date',
        'business_date' => 'date',
        'is_current' => 'boolean',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'audited_at' => 'datetime',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audited_by');
    }

    public static function getCurrentForHotel(int $hotelId): ?self
    {
        return static::where('hotel_id', $hotelId)
            ->where('is_current', true)
            ->first();
    }

    public static function openNewBusinessDate(int $hotelId, ?int $userId = null): self
    {
        $current = static::getCurrentForHotel($hotelId);
        if ($current) {
            $current->update(['is_current' => false]);
        }

        return static::create([
            'hotel_id' => $hotelId,
            'actual_date' => now(),
            'business_date' => now(),
            'status' => 'open',
            'is_current' => true,
            'opened_at' => now(),
            'opened_by' => $userId,
        ]);
    }

    public function close(): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
            'is_current' => false,
        ]);
    }
}
