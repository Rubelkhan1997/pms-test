<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Observers;

use App\Modules\Housekeeping\Events\HousekeepingTaskCreated;
use App\Modules\Housekeeping\Models\HousekeepingTask;

class HousekeepingTaskObserver
{
    /**
     * Handle the model created event.
     */
    public function created(HousekeepingTask $entity): void
    {
        HousekeepingTaskCreated::dispatch($entity);
    }
}

