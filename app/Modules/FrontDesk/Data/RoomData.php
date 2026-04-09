<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Data;

use App\Enums\RoomStatus;
use Spatie\LaravelData\Data;

class RoomData extends Data
{
    /**
     * Create a new data object.
     */
    public function __construct(
        public int $hotel_id,
        public string $number,
        public string $type,
        public RoomStatus $status,
        public float $base_rate,
        public ?string $floor = null,
    ) {
    }
}
