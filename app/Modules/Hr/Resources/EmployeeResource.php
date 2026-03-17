<?php

declare(strict_types=1);

namespace App\Modules\Hr\Resources;

use App\Http\Resources\UserResource;
use App\Modules\Guest\Resources\HotelResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'user_id' => $this->user_id,
            'reference' => $this->reference,
            'status' => $this->status,
            'department' => $this->department,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relationships
            'hotel' => new HotelResource($this->whenLoaded('hotel')),
            'user' => new UserResource($this->whenLoaded('user')),
            'creator' => new UserResource($this->whenLoaded('createdBy')),
        ];
    }
}
