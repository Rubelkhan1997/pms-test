<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Listeners;

use App\Modules\FrontDesk\Events\ReservationCheckedOut;
use App\Modules\Housekeeping\Actions\CreateHousekeepingTaskAction;
use Illuminate\Support\Facades\Log;

class SendReservationCheckedOutNotification
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly CreateHousekeepingTaskAction $createHousekeepingTask
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(ReservationCheckedOut $event): void
    {
        $reservation = $event->reservation;
        
        // Log the check-out
        Log::info('Reservation checked out', [
            'reservation_id' => $reservation->id,
            'reference' => $reservation->reference,
            'guest' => $reservation->guestProfile?->full_name,
            'room' => $reservation->room?->number,
            'checked_out_at' => $reservation->actual_check_out,
        ]);
        
        // Create housekeeping task for room cleaning
        $this->createHousekeepingTask->execute([
            'hotel_id' => $reservation->hotel_id,
            'room_id' => $reservation->room_id,
            'task_type' => 'cleaning',
            'priority' => 'normal',
            'description' => 'Room cleaning after guest check-out',
        ]);
        
        // TODO: Send email/SMS notification to guest
        // TODO: Send notification to housekeeping team
        // TODO: Update channel manager (OTA) if needed
    }
}
