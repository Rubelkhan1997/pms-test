<?php

declare(strict_types=1);

namespace App\Modules\Guest\Listeners;

use App\Modules\Guest\Events\GuestProfileCreated;
use Illuminate\Support\Facades\Log;

class SendGuestWelcomeNotification
{
    /**
     * Handle the event.
     */
    public function handle(GuestProfileCreated $event): void
    {
        Log::info('GuestProfileCreated handled', ['id' => $event->entity->id]);
    }
}

