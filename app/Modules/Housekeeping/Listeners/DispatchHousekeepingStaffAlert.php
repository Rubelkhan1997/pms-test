<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Listeners;

use App\Modules\Housekeeping\Events\HousekeepingTaskCreated;
use Illuminate\Support\Facades\Log;

class DispatchHousekeepingStaffAlert
{
    /**
     * Handle the event.
     */
    public function handle(HousekeepingTaskCreated $event): void
    {
        Log::info('HousekeepingTaskCreated handled', ['id' => $event->entity->id]);
    }
}

