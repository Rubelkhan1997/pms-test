<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'propertyId'  => $this->property_id,
            'name'        => $this->name,
            'code'        => $this->code,
            'type'        => $this->type,
            'floor'       => $this->floor,
            'maxOccupancy' => $this->max_occupancy,
            'adultOccupancy' => $this->adult_occupancy,
            'numBedrooms'  => $this->num_bedrooms,
            'numBathrooms' => $this->num_bathrooms,
            'areaSqm'     => $this->area_sqm,
            'bedTypes'    => $this->bed_types,
            'baseRate'    => $this->base_rate,
            'amenities'   => $this->amenities,
            'isActive'    => $this->is_active,
            'roomCount'   => $this->rooms()->count(),
            'createdAt'   => $this->created_at,
        ];
    }
}