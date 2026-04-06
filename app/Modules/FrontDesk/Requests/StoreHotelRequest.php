<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHotelRequest extends FormRequest
{
    /**
     * Determine whether the user is authorized.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create hotels');
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'code'     => ['required', 'string', 'max:50', 'unique:hotels,code'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'max:10'],
            'email'    => ['nullable', 'email', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:50'],
            'address'  => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required'     => 'Hotel name is required',
            'name.max'          => 'Hotel name cannot exceed 255 characters',
            'code.required'     => 'Hotel code is required',
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
