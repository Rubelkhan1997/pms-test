<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Resources;

use App\Http\Resources\UserResource;
use App\Modules\FrontDesk\Resources\RoomResource;
use App\Modules\Guest\Resources\HotelResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HousekeepingTaskResource extends JsonResource
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
            'room_id' => $this->room_id,
            'reference' => $this->reference,
            'task_type' => $this->task_type,
            'status' => $this->status->value,
            'priority' => $this->priority,
            'description' => $this->description,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relationships
            'room' => new RoomResource($this->whenLoaded('room')),
            'hotel' => new HotelResource($this->whenLoaded('hotel')),
            'created_by' => new UserResource($this->whenLoaded('createdBy')),
            'assigned_to' => new UserResource($this->whenLoaded('assignedTo')),
        ];
    }
}
