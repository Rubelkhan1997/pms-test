<?php

declare(strict_types=1);

namespace App\Modules\Guest\Resources;

use App\Http\Resources\UserResource;
use App\Modules\Guest\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuestProfileResource extends JsonResource
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
            'agent_id' => $this->agent_id,
            'reference' => $this->reference,
            'status' => $this->status,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relationships
            'hotel' => new HotelResource($this->whenLoaded('hotel')),
            'agent' => new AgentResource($this->whenLoaded('agent')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'reservations_count' => $this->whenCounted('reservations'),
        ];
    }
}
