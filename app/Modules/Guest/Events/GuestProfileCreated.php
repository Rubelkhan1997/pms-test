<?php

declare(strict_types=1);

namespace App\Modules\Guest\Events;

use App\Modules\Guest\Models\GuestProfile;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GuestProfileCreated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public readonly GuestProfile $entity)
    {
    }
}

