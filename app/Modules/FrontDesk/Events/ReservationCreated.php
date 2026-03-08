<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Events;

use App\Modules\FrontDesk\Models\Reservation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationCreated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public readonly Reservation $entity)
    {
    }
}

