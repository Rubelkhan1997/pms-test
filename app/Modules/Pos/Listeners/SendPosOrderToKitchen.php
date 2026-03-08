<?php

declare(strict_types=1);

namespace App\Modules\Pos\Listeners;

use App\Modules\Pos\Events\PosOrderPlaced;
use Illuminate\Support\Facades\Log;

class SendPosOrderToKitchen
{
    /**
     * Handle the event.
     */
    public function handle(PosOrderPlaced $event): void
    {
        Log::info('PosOrderPlaced handled', ['id' => $event->entity->id]);
    }
}

