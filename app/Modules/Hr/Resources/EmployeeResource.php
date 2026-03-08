<?php

declare(strict_types=1);

namespace App\Modules\Hr\Resources;

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
            'reference' => $this->reference,
            'status' => $this->status,
            'scheduled_at' => $this->scheduled_at,
            'meta' => $this->meta,
        ];
    }
}

