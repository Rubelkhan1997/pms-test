<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait HasHotel
 * 
 * Provides multi-tenancy support by automatically scoping queries to the current hotel
 * and auto-assigning the hotel_id on model creation.
 * 
 * @property-read int $hotel_id
 * @property-read Hotel $hotel
 */
trait HasHotel
{
    /**
     * Boot the trait
     */
    protected static function bootHasHotel(): void
    {
        // Add global scope to filter by current hotel
        static::addGlobalScope('hotel', function (Builder $builder) {
            if ($hotelId = currentHotel()?->id) {
                $builder->where($builder->getModel()->getTable() . '.hotel_id', $hotelId);
            }
        });
        
        // Auto-assign hotel on create
        static::creating(function ($model) {
            if (!$model->hotel_id && $hotelId = currentHotel()?->id) {
                $model->hotel_id = $hotelId;
            }
        });
    }
    
    /**
     * Get the hotel that owns this model
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
    
    /**
     * Scope to include all hotels (for super admin)
     */
    public function scopeAllHotels(Builder $query): Builder
    {
        return $query->withoutGlobalScope('hotel');
    }
    
    /**
     * Scope to filter by specific hotel
     */
    public function scopeForHotel(Builder $query, int $hotelId): Builder
    {
        return $query->where('hotel_id', $hotelId);
    }
}
