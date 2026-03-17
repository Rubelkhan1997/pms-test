<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Services;

use App\Modules\FrontDesk\Models\Room;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Models\RoomType;
use App\Modules\FrontDesk\Enums\RoomStatus;
use Carbon\Carbon;

/**
 * Room Assignment Service
 * 
 * Automatically assigns rooms to reservations:
 * - Find best available room
 * - Consider preferences (floor, view, smoking)
 * - Handle room moves
 * - Optimize room occupancy
 */
class RoomAssignmentService
{
    /**
     * Auto-assign room to reservation.
     *
     * @param  Reservation  $reservation  Reservation model
     * @param  array<string, mixed>  $preferences  Room preferences
     * @return Room|null Assigned room or null if none available
     */
    public function assignRoom(
        Reservation $reservation,
        array $preferences = []
    ): ?Room {
        if (!$reservation->room_type_id) {
            return null;
        }
        
        $room = $this->findBestAvailableRoom(
            $reservation->room_type_id,
            $reservation->check_in_date,
            $reservation->check_out_date,
            $preferences
        );
        
        if ($room) {
            $reservation->update(['room_id' => $room->id]);
        }
        
        return $room;
    }
    
    /**
     * Find best available room for reservation.
     *
     * @param  int  $roomTypeId  Room type ID
     * @param  Carbon  $checkIn  Check-in date
     * @param  Carbon  $checkOut  Check-out date
     * @param  array<string, mixed>  $preferences  Room preferences
     * @return Room|null
     */
    public function findBestAvailableRoom(
        int $roomTypeId,
        Carbon $checkIn,
        Carbon $checkOut,
        array $preferences = []
    ): ?Room {
        $roomType = RoomType::findOrFail($roomTypeId);
        
        // Get all rooms of this type
        $rooms = $roomType->rooms()
            ->where('status', RoomStatus::Available->value)
            ->get();
        
        // Filter out rooms with conflicting reservations
        $availableRooms = $rooms->filter(function ($room) use ($checkIn, $checkOut) {
            return !$this->hasConflictingReservation($room, $checkIn, $checkOut);
        });
        
        if ($availableRooms->isEmpty()) {
            return null;
        }
        
        // Score rooms based on preferences
        return $this->scoreAndSelectBestRoom($availableRooms, $preferences);
    }
    
    /**
     * Check if room has conflicting reservation.
     */
    protected function hasConflictingReservation(
        Room $room,
        Carbon $checkIn,
        Carbon $checkOut
    ): bool {
        return $room->reservations()
            ->whereIn('status', [
                \App\Modules\FrontDesk\Enums\ReservationStatus::Confirmed->value,
                \App\Modules\FrontDesk\Enums\ReservationStatus::CheckedIn->value,
            ])
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where(function ($q) use ($checkIn, $checkOut) {
                    $q->whereBetween('check_in_date', [$checkIn, $checkOut])
                      ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                      ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                          $q2->where('check_in_date', '<=', $checkIn)
                             ->where('check_out_date', '>=', $checkOut);
                      });
                });
            })
            ->exists();
    }
    
    /**
     * Score rooms and select best match.
     *
     * @param  \Illuminate\Support\Collection<int, Room>  $rooms
     * @param  array<string, mixed>  $preferences
     * @return Room
     */
    protected function scoreAndSelectBestRoom($rooms, array $preferences): Room
    {
        $scoredRooms = $rooms->map(function ($room) use ($preferences) {
            $score = 0;
            
            // Floor preference
            if (isset($preferences['floor'])) {
                if ($room->floor == $preferences['floor']) {
                    $score += 10;
                } elseif (abs((int) $room->floor - (int) $preferences['floor']) === 1) {
                    $score += 5; // Adjacent floor
                }
            }
            
            // View preference
            if (isset($preferences['view_type'])) {
                if ($room->view_type == $preferences['view_type']) {
                    $score += 10;
                }
            }
            
            // Smoking preference
            if (isset($preferences['smoking'])) {
                if ($room->smoking == $preferences['smoking']) {
                    $score += 10;
                }
            }
            
            // Prefer rooms that haven't been used recently (for housekeeping rotation)
            $lastUsed = $room->reservations()
                ->latest('check_out_date')
                ->first();
            
            if ($lastUsed && $lastUsed->check_out_date) {
                $daysSinceLastUse = $lastUsed->check_out_date->diffInDays(now());
                if ($daysSinceLastUse > 7) {
                    $score += 5; // Prefer rooms not used recently
                }
            }
            
            // Prefer lower floor numbers (easier access)
            if ($room->floor) {
                $score += max(0, 10 - (int) $room->floor);
            }
            
            return ['room' => $room, 'score' => $score];
        });
        
        // Select room with highest score
        $bestRoom = $scoredRooms->sortByDesc('score')->first();
        
        return $bestRoom['room'];
    }
    
    /**
     * Change room assignment for reservation.
     *
     * @param  Reservation  $reservation  Reservation model
     * @param  int  $newRoomId  New room ID
     * @return bool Success
     */
    public function changeRoom(
        Reservation $reservation,
        int $newRoomId
    ): bool {
        $newRoom = Room::findOrFail($newRoomId);
        
        // Check if new room is available for the dates
        if (!$this->isRoomAvailable($newRoom, $reservation->check_in_date, $reservation->check_out_date)) {
            return false;
        }
        
        // Update reservation
        $reservation->update(['room_id' => $newRoomId]);
        
        return true;
    }
    
    /**
     * Check if specific room is available for dates.
     */
    public function isRoomAvailable(
        Room $room,
        Carbon $checkIn,
        Carbon $checkOut
    ): bool {
        // Check room status
        if ($room->status !== RoomStatus::Available->value) {
            return false;
        }
        
        // Check for conflicting reservations
        return !$this->hasConflictingReservation($room, $checkIn, $checkOut);
    }
    
    /**
     * Get available rooms for room type and dates.
     *
     * @param  int  $roomTypeId  Room type ID
     * @param  Carbon  $checkIn  Check-in date
     * @param  Carbon  $checkOut  Check-out date
     * @return \Illuminate\Database\Eloquent\Collection<int, Room>
     */
    public function getAvailableRooms(
        int $roomTypeId,
        Carbon $checkIn,
        Carbon $checkOut
    ) {
        $roomType = RoomType::findOrFail($roomTypeId);
        
        return $roomType->rooms()
            ->where('status', RoomStatus::Available->value)
            ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                $query->whereIn('status', [
                    \App\Modules\FrontDesk\Enums\ReservationStatus::Confirmed->value,
                    \App\Modules\FrontDesk\Enums\ReservationStatus::CheckedIn->value,
                ])
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
    
    /**
     * Bulk assign rooms for multiple reservations.
     *
     * @param  \Illuminate\Support\Collection<int, Reservation>  $reservations
     * @return array{assigned: int, failed: int, details: array}
     */
    public function bulkAssign($reservations): array
    {
        $assigned = 0;
        $failed = 0;
        $details = [];
        
        foreach ($reservations as $reservation) {
            $room = $this->assignRoom($reservation);
            
            if ($room) {
                $assigned++;
                $details[] = [
                    'reservation_id' => $reservation->id,
                    'room_id' => $room->id,
                    'room_number' => $room->number,
                    'success' => true,
                ];
            } else {
                $failed++;
                $details[] = [
                    'reservation_id' => $reservation->id,
                    'room_id' => null,
                    'reason' => 'No available rooms',
                    'success' => false,
                ];
            }
        }
        
        return [
            'assigned' => $assigned,
            'failed' => $failed,
            'details' => $details,
        ];
    }
}
