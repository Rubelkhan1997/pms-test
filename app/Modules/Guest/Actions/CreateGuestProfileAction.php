<?php

declare(strict_types=1);

namespace App\Modules\Guest\Actions;

use App\Modules\Guest\Models\GuestProfile;

class CreateGuestProfileAction
{
    /**
     * Persist a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function __invoke(array $payload): GuestProfile
    {
        return GuestProfile::query()->create($payload);
    }
}

