<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Services;

use App\Modules\FrontDesk\Models\RoomType;
use App\Modules\FrontDesk\Models\Room;
use App\Modules\FrontDesk\Models\RoomAvailability;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Enums\ReservationStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Availability Service
 * 
 * Manages real-time room availability:
 * - Check availability for dates
 * - Update availability on booking
 * - Inventory management
 * - Overbooking control
 */
class AvailabilityService
{
    /**
     * Check availability for room type and dates.
     *
     * @param  int  $roomTypeId  Room type ID
     * @param  Carbon  $checkIn  Check-in date
     * @param  Carbon  $checkOut  Check-out date
     * @param  int  $quantity  Number of rooms needed
     * @return array{
     *     available: bool,
     *     available_rooms: int,
     *     requested: int,
     *     dates: array
     * }
     */
    public function checkAvailability(
        int $roomTypeId,
        Carbon $checkIn,
        Carbon $checkOut,
        int $quantity = 1
    ): array {
        $roomType = RoomType::findOrFail($roomTypeId);
        $totalRooms = $roomType->rooms()->count();
        
        $dates = [];
        $currentDate = $checkIn->copy();
        $minAvailable = PHP_INT_MAX;
        
        while ($currentDate < $checkOut) {
            $booked = $this->getBookedRooms($roomTypeId, $currentDate);
            $outOfOrder = $this->getOutOfOrderRooms($roomTypeId, $currentDate);
            $available = $totalRooms - $booked - $outOfOrder;
            
            $dates[] = [
                'date' => $currentDate->toDateString(),
                'total' => $totalRooms,
                'booked' => $booked,
                'out_of_order' => $outOfOrder,
                'available' => max(0, $available),
            ];
            
            $minAvailable = min($minAvailable, $available);
            $currentDate->addDay();
        }
        
        return [
            'available' => $minAvailable >= $quantity,
            'available_rooms' => $minAvailable,
            'requested' => $quantity,
            'dates' => $dates,
            'room_type' => $roomType->only(['id', 'name', 'code']),
        ];
    }
    
    /**
     * Get number of booked rooms for date.
     */
    protected function getBookedRooms(int $roomTypeId, Carbon $date): int
    {
        return Reservation::where('room_type_id', $roomTypeId)
            ->whereIn('status', [
                ReservationStatus::Confirmed->value,
                ReservationStatus::CheckedIn->value,
            ])
            ->where('check_in_date', '<=', $date)
            ->where('check_out_date', '>', $date)
            ->count();
    }
    
    /**
     * Get number of out-of-order rooms for date.
     */
    protected function getOutOfOrderRooms(int $roomTypeId, Carbon $date): int
    {
        return Room::where('room_type_id', $roomTypeId)
            ->where('status', 'out_of_order')
            ->count();
    }
    
    /**
     * Search available room types for dates.
     *
     * @param  Carbon  $checkIn  Check-in date
     * @param  Carbon  $checkOut  Check-out date
     * @param  int  $adults  Number of adults
     * @param  int  $children  Number of children
     * @param  int  $quantity  Number of rooms needed
     * @return Collection<int, array{room_type: RoomType, available: bool, available_rooms: int}>
     */
    public function searchAvailableRoomTypes(
        Carbon $checkIn,
        Carbon $checkOut,
        int $adults = 2,
        int $children = 0,
        int $quantity = 1
    ): Collection {
        $roomTypes = RoomType::where('is_active', true)
            ->where('max_adults', '>=', $adults)
            ->where('max_children', '>=', $children)
            ->where('max_occupancy', '>=', ($adults + $children))
            ->get();
        
        $availableTypes = collect();
        
        foreach ($roomTypes as $roomType) {
            $availability = $this->checkAvailability(
                $roomType->id,
                $checkIn,
                $checkOut,
                $quantity
            );
            
            if ($availability['available']) {
                $availableTypes->push([
                    'room_type' => $roomType,
                    'available_rooms' => $availability['available_rooms'],
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                ]);
            }
        }
        
        return $availableTypes;
    }
    
    /**
     * Update availability when booking is created.
     */
    public function onBookingCreated(Reservation $reservation): void
    {
        if (!$reservation->room_type_id) {
            return;
        }
        
        $currentDate = $reservation->check_in_date;
        while ($currentDate < $reservation->check_out_date) {
            $this->updateDailyAvailability(
                $reservation->hotel_id,
                $reservation->room_type_id,
                $currentDate
            );
            $currentDate->addDay();
        }
    }
    
    /**
     * Update availability when booking is cancelled.
     */
    public function onBookingCancelled(Reservation $reservation): void
    {
        $this->onBookingCreated($reservation); // Same logic, recalculate
    }
    
    /**
     * Update daily availability for a specific date.
     */
    public function updateDailyAvailability(
        int $hotelId,
        int $roomTypeId,
        Carbon $date
    ): void {
        $totalRooms = Room::where('hotel_id', $hotelId)
            ->where('room_type_id', $roomTypeId)
            ->count();
        
        $booked = $this->getBookedRooms($roomTypeId, $date);
        $outOfOrder = $this->getOutOfOrderRooms($roomTypeId, $date);
        $available = max(0, $totalRooms - $booked - $outOfOrder);
        
        RoomAvailability::updateOrCreate(
            [
                'hotel_id' => $hotelId,
                'room_type_id' => $roomTypeId,
                'date' => $date->toDateString(),
            ],
            [
                'total_rooms' => $totalRooms,
                'available_rooms' => $available,
                'booked_rooms' => $booked,
                'out_of_order' => $outOfOrder,
                'out_of_inventory' => 0,
                'is_closed' => $available <= 0,
            ]
        );
    }
    
    /**
     * Get availability calendar for room type.
     *
     * @param  int  $roomTypeId  Room type ID
     * @param  Carbon  $startDate  Start date
     * @param  Carbon  $endDate  End date
     * @return Collection<int, RoomAvailability>
     */
    public function getCalendar(
        int $roomTypeId,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        return RoomAvailability::where('room_type_id', $roomTypeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
    }
    
    /**
     * Close room type for dates (no availability).
     */
    public function closeRoomType(
        int $roomTypeId,
        Carbon $startDate,
        Carbon $endDate
    ): void {
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            RoomAvailability::updateOrCreate(
                [
                    'room_type_id' => $roomTypeId,
                    'date' => $currentDate->toDateString(),
                ],
                ['is_closed' => true]
            );
            $currentDate->addDay();
        }
    }
    
    /**
     * Open room type for dates.
     */
    public function openRoomType(
        int $roomTypeId,
        Carbon $startDate,
        Carbon $endDate
    ): void {
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $this->updateDailyAvailability(
                RoomType::find($roomTypeId)->hotel_id,
                $roomTypeId,
                $currentDate
            );
            $currentDate->addDay();
        }
    }
    
    /**
     * Check overbooking limit.
     */
    public function canOverbook(int $roomTypeId, Carbon $date): bool
    {
        $overbookingSetting = \App\Modules\FrontDesk\Models\OverbookingSetting::where('hotel_id', RoomType::find($roomTypeId)->hotel_id)
            ->where(function ($q) use ($roomTypeId) {
                $q->whereNull('room_type_id')
                  ->orWhere('room_type_id', $roomTypeId);
            })
            ->where('is_active', true)
            ->where(function ($q) use ($date) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', $date)
                  ->where(function ($q2) use ($date) {
                      $q2->whereNull('end_date')
                        ->orWhere('end_date', '>=', $date);
                  });
            })
            ->first();
        
        if (!$overbookingSetting) {
            return false;
        }
        
        return $overbookingSetting->overbook_limit > 0 || 
               $overbookingSetting->overbook_percentage > 0;
    }
    
    /**
     * Get overbooking limit for room type and date.
     */
    public function getOverbookingLimit(int $roomTypeId, Carbon $date): int
    {
        $overbookingSetting = \App\Modules\FrontDesk\Models\OverbookingSetting::where('hotel_id', RoomType::find($roomTypeId)->hotel_id)
            ->where(function ($q) use ($roomTypeId) {
                $q->whereNull('room_type_id')
                  ->orWhere('room_type_id', $roomTypeId);
            })
            ->where('is_active', true)
            ->first();
        
        if (!$overbookingSetting) {
            return 0;
        }
        
        $totalRooms = Room::where('room_type_id', $roomTypeId)->count();
        
        if ($overbookingSetting->overbook_percentage > 0) {
            return (int) ceil($totalRooms * ($overbookingSetting->overbook_percentage / 100));
        }
        
        return $overbookingSetting->overbook_limit;
    }
}
