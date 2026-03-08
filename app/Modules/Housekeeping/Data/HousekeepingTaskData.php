<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Data;

use Spatie\LaravelData\Data;

class HousekeepingTaskData extends Data
{
    /**
     * Create a new data object.
     */
    public function __construct(
        public int $hotel_id,
        public string $reference,
        public string $status,
        public ?string $scheduled_at,
        public array $meta = [],
    ) {
    }
}

