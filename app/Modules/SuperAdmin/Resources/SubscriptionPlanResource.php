<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'slug'             => $this->slug,
            'property_limit'   => $this->property_limit,
            'room_limit'       => $this->room_limit,
            'price_monthly'    => $this->price_monthly,
            'price_annual'     => $this->price_annual,
            'trial_enabled'    => $this->trial_enabled,
            'trial_days'       => $this->trial_days,
            'modules_included' => $this->modules_included,
            'is_active'        => $this->is_active,
            'created_at'       => $this->created_at,
        ];
    }
}
