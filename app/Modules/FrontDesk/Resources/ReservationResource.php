<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Resources;

use App\Modules\Guest\Resources\GuestProfileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
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
            'guest_profile_id' => $this->guest_profile_id,
            'reference' => $this->reference,
            'status' => $this->status->value,
            'check_in_date' => $this->check_in_date?->toDateString(),
            'check_out_date' => $this->check_out_date?->toDateString(),
            'actual_check_in' => $this->actual_check_in?->toIso8601String(),
            'actual_check_out' => $this->actual_check_out?->toIso8601String(),
            'adults' => $this->adults,
            'children' => $this->children,
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount ?? 0,
            'balance' => $this->total_amount - ($this->paid_amount ?? 0),
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            
            // Relationships
            'room' => new RoomResource($this->whenLoaded('room')),
            'guest' => new GuestProfileResource($this->whenLoaded('guestProfile')),
            'creator' => new UserResource($this->whenLoaded('createdBy')),
        ];
    }
}
