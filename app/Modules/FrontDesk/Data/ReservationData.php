<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Data;

use Spatie\LaravelData\Data;

class ReservationData extends Data
{
    /**
     * Create a new data object.
     */
    public function __construct(
        public ?int $hotel_id = null,
        public int $guest_id,
        public int $room_id,
        public string $check_in_date,
        public string $check_out_date,
        public float $total_amount,
        public ?int $adults = null,
        public ?int $children = null,
        public string $status = 'pending',
        public ?string $reference = null,
        public array $meta = [],
    ) {
    }
}

