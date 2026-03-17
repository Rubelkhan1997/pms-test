<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Services;

use App\Modules\FrontDesk\Models\RoomType;
use App\Modules\FrontDesk\Models\RatePlan;
use App\Modules\FrontDesk\Models\SeasonalPricing;
use App\Modules\FrontDesk\Models\DailyRate;
use Carbon\Carbon;

/**
 * Pricing Service
 * 
 * Calculates room rates with all pricing adjustments:
 * - Base rate
 * - Seasonal pricing
 * - Daily rate overrides
 * - Length of stay discounts
 * - Occupancy-based pricing
 */
class PricingService
{
    /**
     * Calculate total price for stay.
     *
     * @param  int  $roomTypeId  Room type ID
     * @param  int|null  $ratePlanId  Rate plan ID (optional, uses default BAR)
     * @param  Carbon  $checkIn  Check-in date
     * @param  Carbon  $checkOut  Check-out date
     * @param  int  $adults  Number of adults
     * @param  int  $children  Number of children
     * @return array{
     *     base_total: float,
     *     adjustments: array,
     *     taxes: float,
     *     service_charges: float,
     *     grand_total: float,
     *     nightly_breakdown: array,
     *     average_nightly_rate: float
     * }
     */
    public function calculateStayPrice(
        int $roomTypeId,
        ?int $ratePlanId,
        Carbon $checkIn,
        Carbon $checkOut,
        int $adults = 2,
        int $children = 0
    ): array {
        $roomType = RoomType::findOrFail($roomTypeId);
        $ratePlan = $ratePlanId 
            ? RatePlan::findOrFail($ratePlanId)
            : $this->getDefaultRatePlan($roomTypeId);
        
        $nights = $checkIn->diffInDays($checkOut);
        $nightlyRates = [];
        $baseTotal = 0;
        $adjustments = [];
        
        // Calculate rate for each night
        $currentDate = $checkIn->copy();
        for ($i = 0; $i < $nights; $i++) {
            $rate = $this->calculateNightlyRate($ratePlan, $currentDate, $roomType);
            $nightlyRates[] = [
                'date' => $currentDate->toDateString(),
                'base_rate' => $rate['base'],
                'seasonal_adjustment' => $rate['seasonal'],
                'daily_override' => $rate['daily_override'],
                'final_rate' => $rate['final'],
            ];
            
            $baseTotal += $rate['final'];
            $currentDate->addDay();
        }
        
        // Calculate adjustments
        $adjustments = $this->calculateAdjustments($ratePlan, $checkIn, $checkOut, $adults, $children);
        
        // Calculate taxes and service charges
        $taxes = $this->calculateTaxes($baseTotal, $roomType->hotel_id);
        $serviceCharges = $this->calculateServiceCharges($baseTotal, $roomType->hotel_id);
        
        $grandTotal = $baseTotal + $adjustments['total'] + $taxes + $serviceCharges;
        
        return [
            'base_total' => $baseTotal,
            'adjustments' => $adjustments,
            'taxes' => $taxes,
            'service_charges' => $serviceCharges,
            'grand_total' => $grandTotal,
            'nightly_breakdown' => $nightlyRates,
            'average_nightly_rate' => $grandTotal / $nights,
            'nights' => $nights,
            'room_type' => $roomType->only(['id', 'name', 'code']),
            'rate_plan' => $ratePlan->only(['id', 'name', 'code']),
        ];
    }
    
    /**
     * Calculate rate for a single night.
     *
     * @return array{base: float, seasonal: float, daily_override: float, final: float}
     */
    protected function calculateNightlyRate(
        RatePlan $ratePlan,
        Carbon $date,
        RoomType $roomType
    ): array {
        $baseRate = $ratePlan->base_rate;
        $seasonalAdjustment = 0;
        $dailyOverride = null;
        
        // Check for daily rate override first
        $dailyRate = DailyRate::where('room_type_id', $roomType->id)
            ->where('rate_plan_id', $ratePlan->id)
            ->where('date', $date->toDateString())
            ->first();
        
        if ($dailyRate && $dailyRate->is_available) {
            $dailyOverride = (float) $dailyRate->rate;
        }
        
        // If no daily override, check seasonal pricing
        if ($dailyOverride === null) {
            $seasonal = SeasonalPricing::where('hotel_id', $roomType->hotel_id)
                ->where('rate_plan_id', $ratePlan->id)
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->where('is_active', true)
                ->first();
            
            if ($seasonal) {
                $seasonalAdjustment = $this->applySeasonalAdjustment($baseRate, $seasonal);
            }
        }
        
        // Calculate final rate
        $finalRate = $dailyOverride ?? ($baseRate + $seasonalAdjustment);
        
        return [
            'base' => $baseRate,
            'seasonal' => $seasonalAdjustment,
            'daily_override' => $dailyOverride,
            'final' => max(0, $finalRate), // Ensure non-negative
        ];
    }
    
    /**
     * Apply seasonal adjustment to base rate.
     */
    protected function applySeasonalAdjustment(float $baseRate, SeasonalPricing $seasonal): float
    {
        return match ($seasonal->type) {
            'percentage' => $baseRate * ($seasonal->adjustment / 100),
            'fixed' => $seasonal->adjustment,
            'override' => $seasonal->adjustment - $baseRate,
            default => 0,
        };
    }
    
    /**
     * Calculate adjustments (discounts, extras).
     *
     * @return array{total: float, items: array}
     */
    protected function calculateAdjustments(
        RatePlan $ratePlan,
        Carbon $checkIn,
        Carbon $checkOut,
        int $adults,
        int $children
    ): array {
        $adjustments = [];
        $total = 0;
        $nights = $checkIn->diffInDays($checkOut);
        
        // Length of stay discount
        if ($nights >= 7) {
            $discount = $total * 0.10; // 10% weekly discount
            $adjustments[] = [
                'type' => 'weekly_discount',
                'description' => 'Weekly stay discount (10%)',
                'amount' => -$discount,
            ];
            $total -= $discount;
        } elseif ($nights >= 3) {
            $discount = $total * 0.05; // 5% extended stay discount
            $adjustments[] = [
                'type' => 'extended_stay_discount',
                'description' => 'Extended stay discount (5%)',
                'amount' => -$discount,
            ];
            $total -= $discount;
        }
        
        // Extra adult charges
        $maxAdults = 2; // Could be configurable per room type
        if ($adults > $maxAdults) {
            $extraAdults = $adults - $maxAdults;
            $extraCharge = $extraAdults * $nights * 20; // $20 per extra adult per night
            $adjustments[] = [
                'type' => 'extra_adult',
                'description' => "Extra adult charge ({$extraAdults} x {$nights} nights)",
                'amount' => $extraCharge,
            ];
            $total += $extraCharge;
        }
        
        return [
            'total' => $total,
            'items' => $adjustments,
        ];
    }
    
    /**
     * Calculate taxes.
     */
    protected function calculateTaxes(float $amount, int $hotelId): float
    {
        // Get hotel tax rate from settings or property_taxes table
        $taxRate = \App\Models\PropertyTax::where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->where('type', 'percentage')
            ->sum('rate');
        
        if ($taxRate === 0) {
            $taxRate = config('app.default_tax_rate', 0);
        }
        
        return $amount * ($taxRate / 100);
    }
    
    /**
     * Calculate service charges.
     */
    protected function calculateServiceCharges(float $amount, int $hotelId): float
    {
        // Get service charge rate from hotel settings
        $serviceChargeRate = \App\Models\Hotel::find($hotelId)?->service_charge_rate ?? 0;
        
        return $amount * ($serviceChargeRate / 100);
    }
    
    /**
     * Get default rate plan for room type (BAR).
     */
    protected function getDefaultRatePlan(int $roomTypeId): ?RatePlan
    {
        return RatePlan::where('room_type_id', $roomTypeId)
            ->where('code', 'BAR')
            ->where('is_active', true)
            ->first();
    }
    
    /**
     * Check if rate plan is available for dates.
     */
    public function isRateAvailable(
        RatePlan $ratePlan,
        Carbon $checkIn,
        Carbon $checkOut
    ): bool {
        $nights = $checkIn->diffInDays($checkOut);
        
        // Check length of stay restrictions
        if ($nights < $ratePlan->min_length_of_stay || 
            $nights > $ratePlan->max_length_of_stay) {
            return false;
        }
        
        // Check booking window
        $daysInAdvance = $checkIn->diffInDays(now(), false);
        if ($daysInAdvance < $ratePlan->booking_window_min || 
            $daysInAdvance > $ratePlan->booking_window_max) {
            return false;
        }
        
        // Check blackout dates
        $blackout = \App\Modules\FrontDesk\Models\BlackoutDate::where('hotel_id', $ratePlan->hotel_id)
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
    
    /**
     * Get best available rate for room type and dates.
     *
     * @return array{rate_plan: RatePlan, total: float, breakdown: array}
     */
    public function getBestRate(
        int $roomTypeId,
        Carbon $checkIn,
        Carbon $checkOut
    ): ?array {
        $ratePlans = RatePlan::where('room_type_id', $roomTypeId)
            ->where('is_active', true)
            ->get();
        
        $bestRate = null;
        $bestRatePlan = null;
        
        foreach ($ratePlans as $ratePlan) {
            if (!$this->isRateAvailable($ratePlan, $checkIn, $checkOut)) {
                continue;
            }
            
            $pricing = $this->calculateStayPrice(
                $roomTypeId,
                $ratePlan->id,
                $checkIn,
                $checkOut
            );
            
            if ($bestRate === null || $pricing['grand_total'] < $bestRate) {
                $bestRate = $pricing['grand_total'];
                $bestRatePlan = $ratePlan;
            }
        }
        
        if ($bestRatePlan === null) {
            return null;
        }
        
        return [
            'rate_plan' => $bestRatePlan,
            'total' => $bestRate,
            'breakdown' => $this->calculateStayPrice(
                $roomTypeId,
                $bestRatePlan->id,
                $checkIn,
                $checkOut
            ),
        ];
    }
}
