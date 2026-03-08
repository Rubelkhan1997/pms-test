<?php

declare(strict_types=1);

namespace App\Modules\Booking\Observers;

use App\Modules\Booking\Events\OtaSyncCompleted;
use App\Modules\Booking\Models\OtaSync;

class OtaSyncObserver
{
    /**
     * Handle the model created event.
     */
    public function created(OtaSync $entity): void
    {
        OtaSyncCompleted::dispatch($entity);
    }
}

