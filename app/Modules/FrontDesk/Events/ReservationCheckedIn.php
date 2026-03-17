<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Events;

use App\Modules\FrontDesk\Models\Reservation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationCheckedIn
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Reservation $reservation
    ) {
    }
}
