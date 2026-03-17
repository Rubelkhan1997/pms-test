<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Rate Plan Model
 * 
 * Represents pricing plans (BAR, Corporate, Non-Refundable, Packages)
 */
class RatePlan extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'name',
        'code',
        'description',
        'type',
        'base_rate',
        'is_refundable',
        'min_length_of_stay',
        'max_length_of_stay',
        'booking_window_min',
        'booking_window_max',
        'includes_breakfast',
        'includes_wifi',
        'includes_parking',
        'inclusions',
        'cancellation_policy',
        'is_active',
        'sort_order',
    ];
    
    protected $casts = [
        'base_rate' => 'decimal:2',
        'is_refundable' => 'boolean',
        'includes_breakfast' => 'boolean',
        'includes_wifi' => 'boolean',
        'includes_parking' => 'boolean',
        'inclusions' => 'array',
        'cancellation_policy' => 'array',
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the hotel that owns this rate plan.
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
    
    /**
     * Get the room type for this rate plan.
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
    
    /**
     * Get seasonal pricing for this rate plan.
     */
    public function seasonalPricing(): HasMany
    {
        return $this->hasMany(SeasonalPricing::class);
    }
    
    /**
     * Get daily rates for this rate plan.
     */
    public function dailyRates(): HasMany
    {
        return $this->hasMany(DailyRate::class);
    }
    
    /**
     * Get reservations using this rate plan.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
    
    /**
     * Calculate rate for specific dates.
     */
    public function getRateForDate(\Carbon\Carbon $date): float
    {
        // Check for daily rate override first
        $dailyRate = $this->dailyRates()
            ->where('date', $date->toDateString())
            ->first();
        
        if ($dailyRate) {
            return (float) $dailyRate->rate;
        }
        
        // Check for seasonal pricing
        $seasonal = $this->seasonalPricing()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('is_active', true)
            ->first();
        
        if ($seasonal) {
            return $this->calculateSeasonalRate($seasonal);
        }
        
        // Return base rate
        return (float) $this->base_rate;
    }
    
    /**
     * Calculate rate with seasonal adjustment.
     */
    protected function calculateSeasonalRate(SeasonalPricing $seasonal): float
    {
        $baseRate = (float) $this->base_rate;
        
        return match ($seasonal->type) {
            'percentage' => $baseRate * (1 + ($seasonal->adjustment / 100)),
            'fixed' => $baseRate + $seasonal->adjustment,
            'override' => (float) $seasonal->adjustment,
            default => $baseRate,
        };
    }
    
    /**
     * Check if rate plan is available for dates.
     */
    public function isAvailable(\Carbon\Carbon $checkIn, \Carbon\Carbon $checkOut): bool
    {
        $nights = $checkIn->diffInDays($checkOut);
        
        // Check length of stay restrictions
        if ($nights < $this->min_length_of_stay || $nights > $this->max_length_of_stay) {
            return false;
        }
        
        // Check booking window
        $daysInAdvance = $checkIn->diffInDays(now(), false);
        if ($daysInAdvance < $this->booking_window_min || $daysInAdvance > $this->booking_window_max) {
            return false;
        }
        
        // Check blackout dates
        $blackout = \App\Modules\FrontDesk\Models\BlackoutDate::where('hotel_id', $this->hotel_id)
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('start_date', [$checkIn, $checkOut])
                  ->orWhereBetween('end_date', [$checkIn, $checkOut])
                  ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                      $q2->where('start_date', '<=', $checkIn)
                         ->where('end_date', '>=', $checkOut);
                  });
            })
            ->exists();
        
        return !$blackout;
    }
}
