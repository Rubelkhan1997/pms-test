<?php

declare(strict_types=1);

namespace App\Modules\Pos\Resources;

use App\Http\Resources\UserResource;
use App\Modules\FrontDesk\Resources\ReservationResource;
use App\Modules\Guest\Resources\HotelResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PosOrderResource extends JsonResource
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
            'reference' => $this->reference,
            'outlet' => $this->outlet,
            'status' => $this->status->value,
            'total_amount' => $this->total_amount,
            'guest_name' => $this->guest_name,
            'room_number' => $this->room_number,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'served_at' => $this->served_at?->toIso8601String(),
            'settled_at' => $this->settled_at?->toIso8601String(),
            'items' => $this->items,
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relationships
            'hotel' => new HotelResource($this->whenLoaded('hotel')),
            'created_by' => new UserResource($this->whenLoaded('createdBy')),
            'reservation' => new ReservationResource($this->whenLoaded('reservation')),
        ];
    }
}
