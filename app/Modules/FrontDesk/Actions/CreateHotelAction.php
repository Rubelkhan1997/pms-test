<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

use App\Modules\FrontDesk\Models\Hotel;

class CreateHotelAction
{
    /**
     * Persist a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function __invoke(array $payload): Hotel
    {
        return Hotel::query()->create($payload);
    }
}
