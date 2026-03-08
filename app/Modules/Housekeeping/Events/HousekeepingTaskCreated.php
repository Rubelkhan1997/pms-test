<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Events;

use App\Modules\Housekeeping\Models\HousekeepingTask;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HousekeepingTaskCreated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public readonly HousekeepingTask $entity)
    {
    }
}

