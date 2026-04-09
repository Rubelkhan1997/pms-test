<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Observers;

use App\Modules\FrontDesk\Models\Room;

class RoomObserver
{
    /**
     * Handle the model created event.
     */
    public function created(Room $room): void
    {
        // No event dispatch needed (Has Events/Jobs = NO)
    }

    /**
     * Handle the model updated event.
     */
    public function updated(Room $room): void
    {
        // No event dispatch needed (Has Events/Jobs = NO)
    }

    /**
     * Handle the model deleted event.
     */
    public function deleted(Room $room): void
    {
        // No event dispatch needed (Has Events/Jobs = NO)
    }
}
