<?php

declare(strict_types=1);

namespace App\Modules\Guest\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hotel_id' => $this->hotel_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'commission_rate' => $this->commission_rate,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
