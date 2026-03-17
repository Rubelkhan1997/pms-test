<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Listeners;

use App\Modules\FrontDesk\Events\ReservationCheckedIn;
use Illuminate\Support\Facades\Log;

class SendReservationCheckedInNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(ReservationCheckedIn $event): void
    {
        $reservation = $event->reservation;
        
        // Log the check-in
        Log::info('Reservation checked in', [
            'reservation_id' => $reservation->id,
            'reference' => $reservation->reference,
            'guest' => $reservation->guestProfile?->full_name,
            'room' => $reservation->room?->number,
            'checked_in_at' => $reservation->actual_check_in,
        ]);
        
        // TODO: Send email/SMS notification to guest
        // TODO: Send notification to front desk team
        // TODO: Update channel manager (OTA) if needed
    }
}
