<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

use App\Modules\FrontDesk\Data\PropertyData;
use App\Modules\FrontDesk\Models\Property;
use Illuminate\Support\Str;

class CreatePropertyAction
{
    public function execute(PropertyData $data): Property
    {
        $slug = $data->slug ?? Str::slug($data->name);

        return Property::create([
            'name'          => $data->name,
            'slug'          => $slug,
            'type'         => $data->type,
            'description'  => $data->description,
            'phone'        => $data->phone,
            'email'        => $data->email,
            'country'      => $data->country,
            'state'        => $data->state,
            'city'         => $data->city,
            'area'         => $data->area,
            'street'       => $data->street,
            'postal_code'  => $data->postalCode,
            'timezone'     => $data->timezone ?? 'UTC',
            'currency'    => $data->currency ?? 'USD',
            'status'       => 'open',
            'business_date' => now()->toDateString(),
        ]);
    }
}