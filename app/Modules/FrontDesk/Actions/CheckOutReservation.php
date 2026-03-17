<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

use App\Modules\FrontDesk\Enums\ReservationStatus;
use App\Modules\FrontDesk\Enums\RoomStatus;
use App\Modules\FrontDesk\Events\ReservationCheckedOut;
use App\Modules\FrontDesk\Models\Reservation;

class CheckOutReservation
{
    /**
     * Execute the action.
     *
     * @throws \InvalidArgumentException
     */
    public function execute(Reservation $reservation): Reservation
    {
        // Validate reservation can be checked out
        $this->validate($reservation);
        
        // Update reservation status
        $reservation->update([
            'status' => ReservationStatus::CheckedOut->value,
            'actual_check_out' => now(),
        ]);
        
        // Update room status to dirty (needs cleaning)
        $reservation->room->update([
            'status' => RoomStatus::Dirty->value,
        ]);
        
        // Dispatch event
        event(new ReservationCheckedOut($reservation));
        
        return $reservation->fresh(['room', 'guestProfile']);
    }

    /**
     * Validate the reservation can be checked out.
     *
     * @throws \InvalidArgumentException
     */
    private function validate(Reservation $reservation): void
    {
        if ($reservation->status !== ReservationStatus::CheckedIn) {
            throw new \InvalidArgumentException('Only checked-in reservations can be checked out');
        }
    }
}
