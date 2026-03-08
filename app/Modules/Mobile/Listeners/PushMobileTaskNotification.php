<?php

declare(strict_types=1);

namespace App\Modules\Mobile\Listeners;

use App\Modules\Mobile\Events\MobileTaskCreated;
use Illuminate\Support\Facades\Log;

class PushMobileTaskNotification
{
    /**
     * Handle the event.
     */
    public function handle(MobileTaskCreated $event): void
    {
        Log::info('MobileTaskCreated handled', ['id' => $event->entity->id]);
    }
}

