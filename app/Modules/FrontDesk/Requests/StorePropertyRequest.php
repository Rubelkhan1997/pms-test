<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:200'],
            'slug'          => ['required', 'string', 'max:100', 'unique:properties,slug'],
            'type'          => ['required', 'in:hotel,resort,apartment,villa,hostel'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'phone'        => ['nullable', 'string', 'max:30'],
            'email'        => ['nullable', 'email', 'max:255'],
            'country'      => ['nullable', 'string', 'max:2'],
            'state'        => ['nullable', 'string', 'max:100'],
            'city'         => ['nullable', 'string', 'max:100'],
            'area'         => ['nullable', 'string', 'max:100'],
            'street'       => ['nullable', 'string', 'max:255'],
            'postal_code'  => ['nullable', 'string', 'max:20'],
            'timezone'     => ['nullable', 'string', 'max:50'],
            'currency'     => ['nullable', 'string', 'max:3'],
        ];
    }
}