<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Actions;

use App\Modules\Housekeeping\Models\HousekeepingTask;

class CreateHousekeepingTaskAction
{
    /**
     * Persist a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function __invoke(array $payload): HousekeepingTask
    {
        return HousekeepingTask::query()->create($payload);
    }
}

