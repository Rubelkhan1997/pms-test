<?php

declare(strict_types=1);

namespace App\Modules\Pos\Actions;

use App\Modules\Pos\Models\PosOrder;

class CreatePosOrderAction
{
    /**
     * Persist a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function __invoke(array $payload): PosOrder
    {
        return PosOrder::query()->create($payload);
    }
}

