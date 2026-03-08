<?php

declare(strict_types=1);

namespace App\Modules\Hr\Listeners;

use App\Modules\Hr\Events\EmployeeCreated;
use Illuminate\Support\Facades\Log;

class SendEmployeeOnboardingNotification
{
    /**
     * Handle the event.
     */
    public function handle(EmployeeCreated $event): void
    {
        Log::info('EmployeeCreated handled', ['id' => $event->entity->id]);
    }
}

