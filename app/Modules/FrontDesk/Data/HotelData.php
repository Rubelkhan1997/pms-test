<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Data;

use Spatie\LaravelData\Data;

class HotelData extends Data
{
    /**
     * Create a new data object.
     */
    public function __construct(
        public string $name,
        public string $code,
        public ?string $timezone = null,
        public ?string $currency = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $address = null,
    ) {
    }
}
