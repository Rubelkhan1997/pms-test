<?php

declare(strict_types=1);

namespace App\Modules\Mobile\Actions;

use App\Modules\Mobile\Models\MobileTask;

class CreateMobileTaskAction
{
    /**
     * Persist a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function __invoke(array $payload): MobileTask
    {
        return MobileTask::query()->create($payload);
    }
}

