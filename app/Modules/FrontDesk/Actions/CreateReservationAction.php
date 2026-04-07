<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;
 
use App\Modules\FrontDesk\Models\Reservation;

class CreateReservationAction
{
    /**
     * Persist a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function __invoke(array $payload): Reservation
    {
        return Reservation::query()->create($payload);
    }
}

