<?php

declare(strict_types=1);

namespace App\Modules\Guest\Observers;

use App\Modules\Guest\Events\GuestProfileCreated;
use App\Modules\Guest\Models\GuestProfile;

class GuestProfileObserver
{
    /**
     * Handle the model created event.
     */
    public function created(GuestProfile $entity): void
    {
        GuestProfileCreated::dispatch($entity);
    }
}

