<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Listeners;

use App\Modules\FrontDesk\Events\ReservationCreated;
use Illuminate\Support\Facades\Log;

class SendReservationCreatedNotification
{
    /**
     * Handle the event.
     */
    public function handle(ReservationCreated $event): void
    {
        Log::info('ReservationCreated handled', ['id' => $event->entity->id]);
    }
}

