<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Room Feature Model
 * 
 * Represents amenities/features available in rooms (WiFi, Mini Bar, Safe, etc.)
 */
class RoomFeature extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'hotel_id',
        'name',
        'category',
        'is_free',
        'price',
        'unit',
        'description',
        'is_active',
    ];
    
    protected $casts = [
        'is_free' => 'boolean',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the hotel that owns this feature.
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
    
    /**
     * Get room types that have this feature.
     */
    public function roomTypes(): BelongsToMany
    {
        return $this->belongsToMany(RoomType::class, 'room_type_features')
            ->withPivot('is_included', 'price_override')
            ->withTimestamps();
    }
}
