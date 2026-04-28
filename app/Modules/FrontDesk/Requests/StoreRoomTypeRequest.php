<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'property_id'   => ['required', 'integer', 'exists:properties,id'],
            'name'          => ['required', 'string', 'max:100'],
            'code'          => ['required', 'string', 'max:20'],
            'type'          => ['required', 'in:room,suite,cottage,villa,dormitory'],
            'floor'         => ['nullable', 'string', 'max:20'],
            'max_occupancy' => ['required', 'integer', 'min:1', 'max:20'],
            'adult_occupancy' => ['required', 'integer', 'min:1', 'max:20'],
            'num_bedrooms'   => ['required', 'integer', 'min:1', 'max:10'],
            'num_bathrooms' => ['required', 'integer', 'min:1', 'max:10'],
            'area_sqm'      => ['nullable', 'numeric', 'min:0'],
            'bed_types'     => ['nullable', 'array'],
            'bed_types.*'   => ['string'],
            'base_rate'    => ['required', 'numeric', 'min:0'],
            'amenities'    => ['nullable', 'array'],
            'amenities.*'  => ['string'],
        ];
    }
}