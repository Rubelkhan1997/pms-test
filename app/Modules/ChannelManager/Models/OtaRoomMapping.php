<?php

declare(strict_types=1);

namespace App\Modules\ChannelManager\Models;

use App\Models\Hotel;
use App\Modules\FrontDesk\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OTA Room Mapping Model
 * 
 * Maps internal room types to OTA room types.
 */
class OtaRoomMapping extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'ota_provider_id',
        'ota_room_id',
        'ota_room_type_id',
        'room_name_ota',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
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
}
