<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'code'         => $this->code,
            'timezone'     => $this->timezone,
            'currency'     => $this->currency,
            'email'        => $this->email,
            'phone'        => $this->phone,
            'address'      => $this->address,
            'created_at'   => $this->created_at?->toISOString(),
            'updated_at'   => $this->updated_at?->toISOString(),
        ];
    }
}
