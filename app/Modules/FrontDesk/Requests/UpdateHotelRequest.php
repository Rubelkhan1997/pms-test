<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHotelRequest extends FormRequest
{
    /**
     * Determine whether the user is authorized.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit hotels');
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $hotelId = $this->route('id');

        return [
            'name'     => ['sometimes', 'string', 'max:255'],
            'code'     => ['sometimes', 'string', 'max:50', Rule::unique('hotels', 'code')->ignore($hotelId)],
            'timezone' => ['sometimes', 'string', 'max:100'],
            'currency' => ['sometimes', 'string', 'max:10'],
            'email'    => ['sometimes', 'email', 'max:255'],
            'phone'    => ['sometimes', 'string', 'max:50'],
            'address'  => ['sometimes', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.max'          => 'Hotel name cannot exceed 255 characters',
            'code.max'          => 'Hotel code cannot exceed 50 characters',
            'code.unique'       => 'This hotel code has already been taken',
            'timezone.max'      => 'Timezone cannot exceed 100 characters',
            'currency.max'      => 'Currency cannot exceed 10 characters',
            'email.email'       => 'Please enter a valid email address',
            'email.max'         => 'Email cannot exceed 255 characters',
            'phone.max'         => 'Phone number cannot exceed 50 characters',
            'address.max'       => 'Address cannot exceed 1000 characters',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'status' => 0,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
