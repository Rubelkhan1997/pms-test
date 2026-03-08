<?php

declare(strict_types=1);

namespace App\Modules\Booking\Listeners;

use App\Modules\Booking\Events\OtaSyncCompleted;
use Illuminate\Support\Facades\Log;

class LogOtaSyncCompleted
{
    /**
     * Handle the event.
     */
    public function handle(OtaSyncCompleted $event): void
    {
        Log::info('OtaSyncCompleted handled', ['id' => $event->entity->id]);
    }
}

