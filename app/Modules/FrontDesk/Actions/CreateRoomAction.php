<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

use App\Modules\FrontDesk\Models\Room;

class CreateRoomAction
{
    /**
     * Persist a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function __invoke(array $payload): Room
    {
        return Room::query()->create($payload);
    }
}
