<?php

declare(strict_types=1);

namespace App\Modules\Booking\Events;

use App\Modules\Booking\Models\OtaSync;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OtaSyncCompleted
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public readonly OtaSync $entity)
    {
    }
}

