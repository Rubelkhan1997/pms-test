<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Data;

use Spatie\LaravelData\Data;

class PropertyData extends Data
{
    public function __construct(
        public readonly string  $name,
        public readonly string  $slug,
        public readonly string $type,
        public readonly ?string $description = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?string $country = null,
        public readonly ?string $state = null,
        public readonly ?string $city = null,
        public readonly ?string $area = null,
        public readonly ?string $street = null,
        public readonly ?string $postalCode = null,
        public readonly ?string $timezone = 'UTC',
        public readonly ?string $currency = 'USD',
    ) {}
}