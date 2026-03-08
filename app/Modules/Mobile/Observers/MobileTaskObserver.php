<?php

declare(strict_types=1);

namespace App\Modules\Mobile\Observers;

use App\Modules\Mobile\Events\MobileTaskCreated;
use App\Modules\Mobile\Models\MobileTask;

class MobileTaskObserver
{
    /**
     * Handle the model created event.
     */
    public function created(MobileTask $entity): void
    {
        MobileTaskCreated::dispatch($entity);
    }
}

