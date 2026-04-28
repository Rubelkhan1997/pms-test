<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Data;

use Spatie\LaravelData\Data;

class SubscriptionPlanData extends Data
{
    public function __construct(
        public readonly string  $name,
        public readonly string  $slug,
        public readonly int     $property_limit,
        public readonly int     $room_limit,
        public readonly float   $price_monthly,
        public readonly float   $price_annual,
        public readonly bool    $trial_enabled,
        public readonly int     $trial_days,
        public readonly ?array  $modules_included,
        public readonly bool    $is_active = true,
    ) {}
}
