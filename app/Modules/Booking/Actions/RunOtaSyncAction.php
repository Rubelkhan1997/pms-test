<?php

declare(strict_types=1);

namespace App\Modules\Booking\Actions;

use App\Modules\Booking\Models\OtaSync;

class RunOtaSyncAction
{
    /**
     * Persist a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function __invoke(array $payload): OtaSync
    {
        return OtaSync::query()->create($payload);
    }
}

