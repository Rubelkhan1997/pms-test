<?php

declare(strict_types=1);

namespace App\Modules\Pos\Resources;

use App\Modules\Guest\Resources\HotelResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
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
            'category' => $this->category,
            'price' => $this->price,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relationships
            'hotel' => new HotelResource($this->whenLoaded('hotel')),
        ];
    }
}
