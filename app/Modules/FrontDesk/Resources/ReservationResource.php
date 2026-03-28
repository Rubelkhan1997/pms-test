<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Resources;

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
            'guest_id' => $this->guest_id,
            'reference' => $this->reference,
            'status' => $this->status,
            'check_in_date' => $this->check_in_date,
            'check_out_date' => $this->check_out_date,
            'total_amount' => $this->total_amount,
            'adults' => $this->adults,
            'children' => $this->children,
            'notes' => $this->notes,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
            
            // ✅ Relations
            'hotel' => $this->whenLoaded('hotel', fn () => [
                'id' => $this->hotel->id,
                'name' => $this->hotel->name,
                'code' => $this->hotel->code,
            ]),
            
            'room' => $this->whenLoaded('room', fn () => [
                'id' => $this->room->id,
                'number' => $this->room->number,
                'type' => $this->room->type,
                'price' => $this->room->price,
                'status' => $this->room->status,
            ]),
            
            'guest' => $this->whenLoaded('guest', fn () => [
                'id' => $this->guest->id,
                'first_name' => $this->guest->first_name,
                'last_name' => $this->guest->last_name,
                'email' => $this->guest->email,
                'phone' => $this->guest->phone,
            ]),
        ];
    }
}

