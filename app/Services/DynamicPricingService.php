<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RoomType;
use App\Models\DailyRate;
use App\Models\RatePlan;
use App\Models\Reservation;
use App\Models\BusinessDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Dynamic Pricing Service
 * 
 * Automatically adjusts rates based on demand, occupancy, and market conditions.
 */
class DynamicPricingService
{
    /**
     * Pricing rules configuration
     */
    private array $rules = [
        'occupancy_thresholds' => [
            ['threshold' => 50, 'adjustment' => 0],
            ['threshold' => 70, 'adjustment' => 10],
            ['threshold' => 85, 'adjustment' => 20],
            ['threshold' => 95, 'adjustment' => 35],
        ],
        'advance_booking_discounts' => [
            ['days_ahead' => 60, 'discount' => 15],
            ['days_ahead' => 30, 'discount' => 10],
            ['days_ahead' => 14, 'discount' => 5],
        ],
        'last_minute_premium' => [
            ['days_before' => 3, 'premium' => 10],
            ['days_before' => 1, 'premium' => 20],
        ],
        'day_of_week_multipliers' => [
            0 => 1.0, // Sunday
            1 => 0.9, // Monday
            2 => 0.9, // Tuesday
            3 => 1.0, // Wednesday
            4 => 1.2, // Thursday
            5 => 1.4, // Friday
            6 => 1.3, // Saturday
        ],
    ];

    /**
     * Calculate dynamic rate for a room type on a specific date
     */
    public function calculateDynamicRate(
        int $roomTypeId,
        Carbon $date,
        ?float $baseRate = null
    ): float {
        if ($baseRate === null) {
            $baseRate = $this->getBaseRate($roomTypeId, $date);
        }

        $rate = $baseRate;

        // Apply occupancy-based adjustment
        $occupancyRate = $this->getOccupancyRate($roomTypeId, $date);
        $rate = $this->applyOccupancyAdjustment($rate, $occupancyRate);

        // Apply day of week multiplier
        $rate = $this->applyDayOfWeekMultiplier($rate, $date);

        // Apply seasonal adjustment
        $rate = $this->applySeasonalAdjustment($rate, $date, $roomTypeId);

        // Apply competitor rate adjustment (if available)
        $rate = $this->applyCompetitorAdjustment($rate, $roomTypeId);

        return round($rate, 2);
    }

    /**
     * Get base rate for room type
     */
    protected function getBaseRate(int $roomTypeId, Carbon $date): float
    {
        $dailyRate = DailyRate::where('room_type_id', $roomTypeId)
            ->where('date', $date->toDateString())
            ->first();

        return $dailyRate ? (float) $dailyRate->rate : 100.0;
    }

    /**
     * Get occupancy rate for room type on date
     */
    protected function getOccupancyRate(int $roomTypeId, Carbon $date): float
    {
        $totalRooms = RoomType::findOrFail($roomTypeId)->rooms()->count();
        if ($totalRooms === 0) {
            return 0;
        }

        $occupiedRooms = Reservation::where('room_type_id', $roomTypeId)
            ->where('check_in', '<=', $date)
            ->where('check_out', '>', $date)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->count();

        return ($occupiedRooms / $totalRooms) * 100;
    }

    /**
     * Apply occupancy-based rate adjustment
     */
    protected function applyOccupancyAdjustment(float $rate, float $occupancyRate): float
    {
        foreach (array_reverse($this->rules['occupancy_thresholds']) as $threshold) {
            if ($occupancyRate >= $threshold['threshold']) {
                return $rate * (1 + ($threshold['adjustment'] / 100));
            }
        }

        return $rate;
    }

    /**
     * Apply day of week multiplier
     */
    protected function applyDayOfWeekMultiplier(float $rate, Carbon $date): float
    {
        $dayOfWeek = (int) $date->format('w');
        $multiplier = $this->rules['day_of_week_multipliers'][$dayOfWeek] ?? 1.0;

        return $rate * $multiplier;
    }

    /**
     * Apply seasonal adjustment
     */
    protected function applySeasonalAdjustment(float $rate, Carbon $date, int $roomTypeId): float
    {
        // Check for seasonal pricing
        $seasonalPricing = DB::table('seasonal_pricing')
            ->where('room_type_id', $roomTypeId)
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->where('is_active', true)
            ->first();

        if ($seasonalPricing) {
            if ($seasonalPricing->adjustment_type === 'percentage') {
                return $rate * (1 + ($seasonalPricing->adjustment_value / 100));
            } else {
                return $rate + $seasonalPricing->adjustment_value;
            }
        }

        return $rate;
    }

    /**
     * Apply competitor rate adjustment
     */
    protected function applyCompetitorAdjustment(float $rate, int $roomTypeId): float
    {
        // Get average competitor rate from database or external API
        $competitorRate = DB::table('competitor_rates')
            ->where('room_type_id', $roomTypeId)
            ->where('date', now()->toDateString())
            ->avg('rate');

        if ($competitorRate && $competitorRate > 0) {
            // Price 5% below average competitor rate
            $targetRate = $competitorRate * 0.95;
            
            // Only adjust if difference is significant (>10%)
            $difference = abs($rate - $targetRate) / $targetRate;
            if ($difference > 0.10) {
                return $targetRate;
            }
        }

        return $rate;
    }

    /**
     * Update rates for a date range
     */
    public function updateRatesForDateRange(
        int $roomTypeId,
        Carbon $startDate,
        Carbon $endDate,
        bool $dryRun = false
    ): array {
        $updatedRates = [];
        $dates = $startDate->daysUntil($endDate->endOfDay());

        foreach ($dates as $date) {
            $newRate = $this->calculateDynamicRate($roomTypeId, $date);
            
            if (!$dryRun) {
                DailyRate::updateOrCreate(
                    [
                        'room_type_id' => $roomTypeId,
                        'date' => $date->toDateString(),
                    ],
                    ['rate' => $newRate]
                );
            }

            $updatedRates[] = [
                'date' => $date->toDateString(),
                'old_rate' => $this->getBaseRate($roomTypeId, $date),
                'new_rate' => $newRate,
            ];
        }

        return $updatedRates;
    }

    /**
     * Get pricing recommendations
     */
    public function getPricingRecommendations(int $roomTypeId, Carbon $date): array
    {
        $currentRate = $this->getBaseRate($roomTypeId, $date);
        $recommendedRate = $this->calculateDynamicRate($roomTypeId, $date, $currentRate);
        $occupancyRate = $this->getOccupancyRate($roomTypeId, $date);
        $competitorRate = DB::table('competitor_rates')
            ->where('room_type_id', $roomTypeId)
            ->where('date', $date->toDateString())
            ->avg('rate');

        return [
            'current_rate' => $currentRate,
            'recommended_rate' => $recommendedRate,
            'change_percentage' => round((($recommendedRate - $currentRate) / $currentRate) * 100, 2),
            'occupancy_rate' => round($occupancyRate, 2),
            'competitor_average' => $competitorRate,
            'day_of_week' => $date->format('l'),
            'recommendation' => $this->getRecommendationText($currentRate, $recommendedRate, $occupancyRate),
        ];
    }

    /**
     * Get human-readable recommendation
     */
    protected function getRecommendationText(float $currentRate, float $recommendedRate, float $occupancyRate): string
    {
        $change = (($recommendedRate - $currentRate) / $currentRate) * 100;

        if ($change > 15) {
            return "High demand detected. Consider increasing rates by " . round($change) . "%.";
        } elseif ($change > 5) {
            return "Moderate demand. Small rate increase recommended.";
        } elseif ($change < -15) {
            return "Low occupancy. Consider decreasing rates by " . abs(round($change)) . "% to attract bookings.";
        } elseif ($change < -5) {
            return "Low demand. Small rate decrease may help.";
        } else {
            return "Rates are optimally positioned for current market conditions.";
        }
    }
}
