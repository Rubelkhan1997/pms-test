<?php

declare(strict_types=1);

namespace App\Modules\Mobile\Events;

use App\Modules\Mobile\Models\MobileTask;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MobileTaskCreated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public readonly MobileTask $entity)
    {
    }
}

