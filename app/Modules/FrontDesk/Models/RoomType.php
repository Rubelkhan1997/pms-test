<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Room Type Model
 * 
 * Represents a category of rooms (Standard, Deluxe, Suite, etc.)
 */
class RoomType extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'hotel_id',
        'name',
        'code',
        'description',
        'max_adults',
        'max_children',
        'max_occupancy',
        'base_rate',
        'bed_type',
        'bed_count',
        'size_sqm',
        'view_type',
        'smoking',
        'amenities',
        'photos',
        'is_active',
        'sort_order',
    ];
    
    protected $casts = [
        'base_rate' => 'decimal:2',
        'size_sqm' => 'decimal:2',
        'smoking' => 'boolean',
        'amenities' => 'array',
        'photos' => 'array',
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the hotel that owns this room type.
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
    
    /**
     * Get rooms of this type.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
    
    /**
     * Get features for this room type.
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(RoomFeature::class, 'room_type_features')
            ->withPivot('is_included', 'price_override')
            ->withTimestamps();
    }
    
    /**
     * Get rate plans for this room type.
     */
    public function ratePlans(): HasMany
    {
        return $this->hasMany(RatePlan::class);
    }
    
    /**
     * Check if room type is available for dates.
     */
    public function isAvailable(\Carbon\Carbon $checkIn, \Carbon\Carbon $checkOut, int $quantity = 1): bool
    {
        return $this->rooms()
            ->where('status', 'available')
            ->count() >= $quantity;
    }
    
    /**
     * Get available rooms for dates.
     */
    public function getAvailableRooms(\Carbon\Carbon $checkIn, \Carbon\Carbon $checkOut): \Illuminate\Database\Eloquent\Collection
    {
        return $this->rooms()
            ->where('status', 'available')
            ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                $query->whereIn('status', ['confirmed', 'checked_in'])
                    ->where(function ($q) use ($checkIn, $checkOut) {
                        $q->whereBetween('check_in_date', [$checkIn, $checkOut])
                          ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                          ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                              $q2->where('check_in_date', '<=', $checkIn)
                                 ->where('check_out_date', '>=', $checkOut);
                          });
                    });
            })
            ->get();
    }
}
