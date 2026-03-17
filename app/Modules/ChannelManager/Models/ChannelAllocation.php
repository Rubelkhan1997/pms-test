<?php

declare(strict_types=1);

namespace App\Modules\ChannelManager\Models;

use App\Models\Hotel;
use App\Modules\FrontDesk\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Channel Allocation Model
 * 
 * Room allocation per channel/OTA.
 */
class ChannelAllocation extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'ota_provider_id',
        'date',
        'allocated_rooms',
        'booked_rooms',
        'is_closed',
    ];
    
    protected $casts = [
        'date' => 'date',
        'is_closed' => 'boolean',
    ];
    
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
    
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
    
    public function otaProvider(): BelongsTo
    {
        return $this->belongsTo(OtaProvider::class);
    }
    
    /**
     * Get available rooms for allocation.
     */
    public function getAvailableRoomsAttribute(): int
    {
        return max(0, $this->allocated_rooms - $this->booked_rooms);
    }
    
    /**
     * Check if channel is open for booking.
     */
    public function isOpen(): bool
    {
        return !$this->is_closed && $this->available_rooms > 0;
    }
    
    /**
     * Increment booked rooms.
     */
    public function incrementBooked(int $amount = 1): void
    {
        $this->increment('booked_rooms', $amount);
    }
    
    /**
     * Decrement booked rooms.
     */
    public function decrementBooked(int $amount = 1): void
    {
        $this->decrement('booked_rooms', $amount);
    }
}
