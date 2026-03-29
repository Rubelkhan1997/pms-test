<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GuestProfile;
use App\Models\Reservation;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Loyalty Program Service
 * 
 * Manages guest loyalty points, tiers, and rewards.
 */
class LoyaltyProgramService
{
    /**
     * Tier configuration
     */
    private array $tiers = [
        'member' => [
            'min_points' => 0,
            'benefits' => ['Free WiFi', 'Late checkout (1 PM)'],
            'points_multiplier' => 1.0,
        ],
        'silver' => [
            'min_points' => 5000,
            'benefits' => ['Free WiFi', 'Late checkout (2 PM)', 'Welcome drink', '5% discount'],
            'points_multiplier' => 1.25,
        ],
        'gold' => [
            'min_points' => 15000,
            'benefits' => ['Free WiFi', 'Late checkout (4 PM)', 'Welcome amenity', '10% discount', 'Room upgrade (subject to availability)'],
            'points_multiplier' => 1.5,
        ],
        'platinum' => [
            'min_points' => 30000,
            'benefits' => ['Free WiFi', 'Late checkout (6 PM)', 'Welcome amenity', '15% discount', 'Complimentary breakfast', 'Guaranteed room upgrade'],
            'points_multiplier' => 2.0,
        ],
    ];

    /**
     * Award points for a stay
     */
    public function awardPointsForStay(Reservation $reservation): int
    {
        $guestProfile = $reservation->guestProfile;
        if (!$guestProfile) {
            return 0;
        }

        $basePoints = $this->calculateBasePoints($reservation);
        $tierMultiplier = $this->getTierMultiplier($guestProfile);
        $totalPoints = (int) ($basePoints * $tierMultiplier);

        // Add points to guest profile
        $guestProfile->increment('loyalty_points', $totalPoints);
        
        // Update tier if necessary
        $this->updateTier($guestProfile);

        // Log points transaction
        DB::table('loyalty_points_transactions')->insert([
            'guest_profile_id' => $guestProfile->id,
            'reservation_id' => $reservation->id,
            'points' => $totalPoints,
            'type' => 'earn',
            'description' => "Stay at {$reservation->checkIn->format('M d, Y')}",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $totalPoints;
    }

    /**
     * Calculate base points for a reservation
     */
    protected function calculateBasePoints(Reservation $reservation): int
    {
        $totalAmount = (float) ($reservation->total_amount ?? 0);
        
        // Base: 10 points per $1 spent
        $basePoints = (int) ($totalAmount * 10);

        // Bonus points for suite bookings
        if ($reservation->room && $reservation->room->roomType) {
            $roomTypeName = strtolower($reservation->room->roomType->name);
            if (str_contains($roomTypeName, 'suite')) {
                $basePoints = (int) ($basePoints * 1.5);
            }
        }

        // Bonus points for long stays
        $nights = $reservation->check_in->diffInDays($reservation->check_out);
        if ($nights >= 7) {
            $basePoints = (int) ($basePoints * 1.2);
        }

        return $basePoints;
    }

    /**
     * Get tier multiplier for guest
     */
    protected function getTierMultiplier(GuestProfile $guestProfile): float
    {
        $tier = $this->getCurrentTier($guestProfile);
        return $this->tiers[$tier]['points_multiplier'];
    }

    /**
     * Get current tier for guest
     */
    public function getCurrentTier(GuestProfile $guestProfile): string
    {
        $points = $guestProfile->loyalty_points ?? 0;

        foreach (['platinum', 'gold', 'silver', 'member'] as $tierName) {
            if ($points >= $this->tiers[$tierName]['min_points']) {
                return $tierName;
            }
        }

        return 'member';
    }

    /**
     * Update guest tier based on points
     */
    public function updateTier(GuestProfile $guestProfile): void
    {
        $currentTier = $guestProfile->loyalty_tier ?? 'member';
        $newTier = $this->getCurrentTier($guestProfile);

        if ($newTier !== $currentTier) {
            $guestProfile->update([
                'loyalty_tier' => $newTier,
                'tier_updated_at' => now(),
            ]);

            // Send tier upgrade notification
            if ($this->isUpgrade($currentTier, $newTier)) {
                $this->sendTierUpgradeNotification($guestProfile, $currentTier, $newTier);
            }
        }
    }

    /**
     * Check if tier change is an upgrade
     */
    protected function isUpgrade(string $oldTier, string $newTier): bool
    {
        $tierOrder = ['member' => 0, 'silver' => 1, 'gold' => 2, 'platinum' => 3];
        return ($tierOrder[$newTier] ?? 0) > ($tierOrder[$oldTier] ?? 0);
    }

    /**
     * Redeem points for reward
     */
    public function redeemPoints(GuestProfile $guestProfile, int $points, string $rewardType): bool
    {
        if ($guestProfile->loyalty_points < $points) {
            return false;
        }

        return DB::transaction(function () use ($guestProfile, $points, $rewardType) {
            $guestProfile->decrement('loyalty_points', $points);
            
            DB::table('loyalty_points_transactions')->insert([
                'guest_profile_id' => $guestProfile->id,
                'points' => -$points,
                'type' => 'redeem',
                'description' => "Redeemed for: {$rewardType}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        });
    }

    /**
     * Get available rewards for guest
     */
    public function getAvailableRewards(GuestProfile $guestProfile): array
    {
        $currentPoints = $guestProfile->loyalty_points ?? 0;
        $tier = $this->getCurrentTier($guestProfile);

        $rewards = [
            [
                'name' => 'Room Upgrade',
                'points' => 5000,
                'description' => 'Complimentary room upgrade (subject to availability)',
                'available' => $currentPoints >= 5000,
            ],
            [
                'name' => 'Late Checkout',
                'points' => 1000,
                'description' => 'Late checkout until 4 PM',
                'available' => $currentPoints >= 1000,
            ],
            [
                'name' => 'Complimentary Breakfast',
                'points' => 2000,
                'description' => 'Full breakfast for two',
                'available' => $currentPoints >= 2000,
            ],
            [
                'name' => 'Spa Credit',
                'points' => 10000,
                'description' => '$100 spa credit',
                'available' => $currentPoints >= 10000 && in_array($tier, ['gold', 'platinum']),
            ],
            [
                'name' => 'Free Night Stay',
                'points' => 25000,
                'description' => 'One complimentary night stay',
                'available' => $currentPoints >= 25000 && in_array($tier, ['platinum']),
            ],
        ];

        return $rewards;
    }

    /**
     * Get tier benefits
     */
    public function getTierBenefits(string $tier = null): array
    {
        if ($tier === null) {
            return $this->tiers;
        }

        return $this->tiers[$tier] ?? $this->tiers['member'];
    }

    /**
     * Send tier upgrade notification
     */
    protected function sendTierUpgradeNotification(GuestProfile $guestProfile, string $oldTier, string $newTier): void
    {
        // Implementation would send email/SMS notification
        // For now, just log the event
        \Log::info("Guest {$guestProfile->email} upgraded from {$oldTier} to {$newTier}");
    }

    /**
     * Get points summary for guest
     */
    public function getPointsSummary(GuestProfile $guestProfile): array
    {
        $currentPoints = $guestProfile->loyalty_points ?? 0;
        $tier = $this->getCurrentTier($guestProfile);
        $nextTier = $this->getNextTier($tier);
        $pointsToNextTier = $nextTier ? ($this->tiers[$nextTier]['min_points'] - $currentPoints) : 0;

        return [
            'current_points' => $currentPoints,
            'current_tier' => $tier,
            'tier_benefits' => $this->tiers[$tier]['benefits'],
            'next_tier' => $nextTier,
            'points_to_next_tier' => max(0, $pointsToNextTier),
            'lifetime_points_earned' => $this->getLifetimePointsEarned($guestProfile),
            'lifetime_points_redeemed' => $this->getLifetimePointsRedeemed($guestProfile),
        ];
    }

    /**
     * Get next tier
     */
    protected function getNextTier(string $currentTier): ?string
    {
        $tierOrder = ['member' => 'silver', 'silver' => 'gold', 'gold' => 'platinum', 'platinum' => null];
        return $tierOrder[$currentTier] ?? null;
    }

    /**
     * Get lifetime points earned
     */
    protected function getLifetimePointsEarned(GuestProfile $guestProfile): int
    {
        return DB::table('loyalty_points_transactions')
            ->where('guest_profile_id', $guestProfile->id)
            ->where('type', 'earn')
            ->sum('points');
    }

    /**
     * Get lifetime points redeemed
     */
    protected function getLifetimePointsRedeemed(GuestProfile $guestProfile): int
    {
        return abs(DB::table('loyalty_points_transactions')
            ->where('guest_profile_id', $guestProfile->id)
            ->where('type', 'redeem')
            ->sum('points'));
    }
}
