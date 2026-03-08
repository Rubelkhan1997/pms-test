<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Observers;

use App\Modules\FrontDesk\Events\ReservationCreated;
use App\Modules\FrontDesk\Models\Reservation;

class ReservationObserver
{
    /**
     * Handle the model created event.
     */
    public function created(Reservation $entity): void
    {
        ReservationCreated::dispatch($entity);
    }
}

