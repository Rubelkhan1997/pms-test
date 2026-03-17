<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

use App\Modules\FrontDesk\Enums\ReservationStatus;
use App\Modules\FrontDesk\Enums\RoomStatus;
use App\Modules\FrontDesk\Events\ReservationCheckedIn;
use App\Modules\FrontDesk\Models\Reservation;

class CheckInReservation
{
    /**
     * Execute the action.
     *
     * @throws \InvalidArgumentException
     */
    public function execute(Reservation $reservation): Reservation
    {
        // Validate reservation can be checked in
        $this->validate($reservation);
        
        // Update reservation status
        $reservation->update([
            'status' => ReservationStatus::CheckedIn->value,
            'actual_check_in' => now(),
        ]);
        
        // Update room status
        $reservation->room->update([
            'status' => RoomStatus::Occupied->value,
        ]);
        
        // Dispatch event
        event(new ReservationCheckedIn($reservation));
        
        return $reservation->fresh(['room', 'guestProfile']);
    }

    /**
     * Validate the reservation can be checked in.
     *
     * @throws \InvalidArgumentException
     */
    private function validate(Reservation $reservation): void
    {
        if ($reservation->status !== ReservationStatus::Confirmed) {
            throw new \InvalidArgumentException('Only confirmed reservations can be checked in');
        }
        
        if ($reservation->room->status !== RoomStatus::Available) {
            throw new \InvalidArgumentException('Room is not available for check-in');
        }
    }
}
