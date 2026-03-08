<?php

declare(strict_types=1);

namespace App\Modules\Pos\Observers;

use App\Modules\Pos\Events\PosOrderPlaced;
use App\Modules\Pos\Models\PosOrder;

class PosOrderObserver
{
    /**
     * Handle the model created event.
     */
    public function created(PosOrder $entity): void
    {
        PosOrderPlaced::dispatch($entity);
    }
}

