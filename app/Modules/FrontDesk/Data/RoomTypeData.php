<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Data;

use Spatie\LaravelData\Data;

class RoomTypeData extends Data
{
    public function __construct(
        public readonly int     $propertyId,
        public readonly string $name,
        public readonly string $code,
        public readonly string $type,
        public readonly ?string $floor = null,
        public readonly int    $maxOccupancy = 2,
        public readonly int    $adultOccupancy = 2,
        public readonly int    $numBedrooms = 1,
        public readonly int    $numBathrooms = 1,
        public readonly ?float $areaSqm = null,
        public readonly ?array $bedTypes = null,
        public readonly float   $baseRate = 0,
        public readonly ?array $amenities = null,
    ) {}
}