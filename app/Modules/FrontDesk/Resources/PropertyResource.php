<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'slug'            => $this->slug,
            'type'           => $this->type,
            'description'    => $this->description,
            'logoPath'       => $this->logo_path,
            'featuredImagePath' => $this->featured_image_path,
            'numberOfRooms'  => $this->number_of_rooms,
            'country'        => $this->country,
            'state'          => $this->state,
            'city'           => $this->city,
            'area'           => $this->area,
            'street'         => $this->street,
            'postalCode'     => $this->postal_code,
            'phone'          => $this->phone,
            'email'          => $this->email,
            'timezone'       => $this->timezone,
            'currency'       => $this->currency,
            'checkInTime'    => $this->check_in_time,
            'checkOutTime'    => $this->check_out_time,
            'status'         => $this->status,
            'businessDate'   => $this->business_date,
            'createdAt'      => $this->created_at,
        ];
    }
}