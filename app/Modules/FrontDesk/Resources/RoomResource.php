<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Resources;

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
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'base_rate' => (float) $this->base_rate,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'hotel' => $this->whenLoaded('hotel', fn () => [
                'id' => $this->hotel->id,
                'name' => $this->hotel->name,
            ]),
        ];
    }
}
