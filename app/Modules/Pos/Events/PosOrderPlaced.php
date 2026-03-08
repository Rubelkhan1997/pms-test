<?php

declare(strict_types=1);

namespace App\Modules\Pos\Events;

use App\Modules\Pos\Models\PosOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosOrderPlaced
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public readonly PosOrder $entity)
    {
    }
}

