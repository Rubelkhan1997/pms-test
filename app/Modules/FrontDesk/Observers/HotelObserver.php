<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Observers;

use App\Modules\FrontDesk\Models\Hotel;

class HotelObserver
{
    /**
     * Handle the model created event.
     */
    public function created(Hotel $hotel): void
    {
        // No event dispatch needed (Has Events/Jobs = NO)
    }

    /**
     * Handle the model updated event.
     */
    public function updated(Hotel $hotel): void
    {
        // No event dispatch needed (Has Events/Jobs = NO)
    }

    /**
     * Handle the model deleted event.
     */
    public function deleted(Hotel $hotel): void
    {
        // No event dispatch needed (Has Events/Jobs = NO)
    }
}
