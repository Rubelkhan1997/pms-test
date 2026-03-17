<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Room Availability Model
 */
class RoomAvailability extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'date',
        'total_rooms',
        'available_rooms',
        'booked_rooms',
        'out_of_order',
        'out_of_inventory',
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
    
    /**
     * Update availability for date.
     */
    public static function updateAvailability(
        int $hotelId,
        int $roomTypeId,
        \Carbon\Carbon $date,
        int $booked = 0
    ): void {
        $totalRooms = Room::where('hotel_id', $hotelId)
            ->where('room_type_id', $roomTypeId)
            ->count();
        
        $outOfOrder = Room::where('hotel_id', $hotelId)
            ->where('room_type_id', $roomTypeId)
            ->where('status', 'out_of_order')
            ->count();
        
        $available = $totalRooms - $booked - $outOfOrder;
        
        static::updateOrCreate(
            [
                'hotel_id' => $hotelId,
                'room_type_id' => $roomTypeId,
                'date' => $date->toDateString(),
            ],
            [
                'total_rooms' => $totalRooms,
                'available_rooms' => max(0, $available),
                'booked_rooms' => $booked,
                'out_of_order' => $outOfOrder,
                'out_of_inventory' => 0,
            ]
        );
    }
}
