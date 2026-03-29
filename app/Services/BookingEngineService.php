<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomAvailability;
use App\Models\RatePlan;
use App\Models\DailyRate;
use App\Models\GuestProfile;
use App\Models\ReservationGuest;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Booking Engine Service
 * 
 * Handles direct booking creation from website booking engine.
 */
class BookingEngineService
{
    /**
     * Search available rooms for given dates
     */
    public function searchAvailability(
        Carbon $checkIn,
        Carbon $checkOut,
        int $adults,
        int $children = 0,
        int $rooms = 1
    ): Collection {
        $nights = $checkIn->diffInDays($checkOut);
        
        // Get all active room types with availability
        return Room::with(['roomType', 'roomType.dailyRates' => function ($query) use ($checkIn, $checkOut) {
            $query->whereBetween('date', [$checkIn->toDateString(), $checkOut->subDay()->toDateString()]);
        }])
        ->whereHas('roomType', function ($query) {
            $query->where('is_active', true);
        })
        ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
            $query->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in', [$checkIn, $checkOut])
                  ->orWhereBetween('check_out', [$checkIn, $checkOut])
                  ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                      $q2->where('check_in', '<=', $checkIn)
                         ->where('check_out', '>=', $checkOut);
                  });
            })
            ->whereNotIn('status', ['cancelled', 'no_show']);
        })
        ->get()
        ->map(function ($room) use ($checkIn, $checkOut, $nights) {
            $totalRate = $room->roomType->dailyRates->sum('rate');
            $averageRate = $nights > 0 ? $totalRate / $nights : 0;
            
            return [
                'room_id' => $room->id,
                'room_type_id' => $room->room_type_id,
                'room_type_name' => $room->roomType->name,
                'room_number' => $room->room_number,
                'max_occupancy' => $room->roomType->max_occupancy,
                'total_rate' => $totalRate,
                'average_rate' => $averageRate,
                'available' => true,
            ];
        });
    }

    /**
     * Create a booking from booking engine
     */
    public function createBooking(array $data): Reservation
    {
        return DB::transaction(function () use ($data) {
            // Create or find guest profile
            $guestProfile = $this->createOrFindGuestProfile($data['guest']);
            
            // Create reservation
            $reservation = Reservation::create([
                'reservation_number' => $this->generateReservationNumber(),
                'room_id' => $data['room_id'],
                'guest_profile_id' => $guestProfile->id,
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'adults' => $data['adults'],
                'children' => $data['children'] ?? 0,
                'status' => 'confirmed',
                'source' => 'website',
                'rate_plan_id' => $data['rate_plan_id'] ?? null,
                'daily_rate' => $data['daily_rate'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Add additional guests
            if (isset($data['additional_guests'])) {
                foreach ($data['additional_guests'] as $guestData) {
                    ReservationGuest::create([
                        'reservation_id' => $reservation->id,
                        'first_name' => $guestData['first_name'],
                        'last_name' => $guestData['last_name'],
                        'email' => $guestData['email'] ?? null,
                        'phone' => $guestData['phone'] ?? null,
                        'type' => 'additional',
                        'is_primary' => false,
                    ]);
                }
            }

            // Add primary guest as reservation guest
            ReservationGuest::create([
                'reservation_id' => $reservation->id,
                'guest_profile_id' => $guestProfile->id,
                'first_name' => $guestProfile->first_name,
                'last_name' => $guestProfile->last_name,
                'email' => $guestProfile->email,
                'phone' => $guestProfile->phone,
                'type' => 'primary',
                'is_primary' => true,
            ]);

            // Process payment if provided
            if (isset($data['payment'])) {
                $this->processPayment($reservation, $data['payment']);
            }

            return $reservation;
        });
    }

    /**
     * Create or find guest profile
     */
    protected function createOrFindGuestProfile(array $guestData): GuestProfile
    {
        // Try to find by email
        if (isset($guestData['email'])) {
            $existing = GuestProfile::where('email', $guestData['email'])->first();
            if ($existing) {
                return $existing;
            }
        }

        // Create new guest profile
        return GuestProfile::create([
            'first_name' => $guestData['first_name'],
            'last_name' => $guestData['last_name'],
            'email' => $guestData['email'] ?? null,
            'phone' => $guestData['phone'] ?? null,
            'country' => $guestData['country'] ?? null,
        ]);
    }

    /**
     * Generate unique reservation number
     */
    protected function generateReservationNumber(): string
    {
        $prefix = 'BE';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -6));
        
        return "{$prefix}-{$date}-{$random}";
    }

    /**
     * Process payment for booking
     */
    protected function processPayment(Reservation $reservation, array $paymentData): void
    {
        // Payment processing logic would go here
        // This would integrate with Stripe, PayPal, etc.
    }

    /**
     * Get booking engine statistics
     */
    public function getStatistics(Carbon $startDate, Carbon $endDate): array
    {
        $reservations = Reservation::where('source', 'website')
            ->whereBetween('check_in', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'no_show']);

        return [
            'total_bookings' => $reservations->count(),
            'total_revenue' => $reservations->sum('total_amount'),
            'average_stay' => $reservations->avg(DB::raw('DATEDIFF(check_out, check_in)')),
            'average_rate' => $reservations->avg('daily_rate'),
            'total_nights' => $reservations->sum(DB::raw('DATEDIFF(check_out, check_in)')),
        ];
    }
}
