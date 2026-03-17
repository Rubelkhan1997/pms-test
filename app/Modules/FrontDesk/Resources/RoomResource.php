<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Resources;

use App\Modules\Guest\Resources\HotelResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
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
            'number' => $this->number,
            'floor' => $this->floor,
            'type' => $this->type,
            'status' => $this->status->value,
            'base_rate' => $this->base_rate,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relationships
            'hotel' => new HotelResource($this->whenLoaded('hotel')),
            'reservations_count' => $this->whenCounted('reservations'),
            
            // Computed
            'is_available' => $this->status->value === 'available',
            'is_occupied' => $this->status->value === 'occupied',
        ];
    }
}
