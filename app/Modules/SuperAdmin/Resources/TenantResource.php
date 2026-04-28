<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'domain'        => $this->domain,
            'database'      => $this->database,
            'status'        => $this->status,
            'is_active'     => $this->isActive(),
            'is_on_trial'   => $this->isOnTrial(),
            'trial_ends_at' => $this->trial_ends_at,
            'contact_name'  => $this->contact_name,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'plan_id'       => $this->plan_id,
            'created_at'    => $this->created_at,
        ];
    }
}