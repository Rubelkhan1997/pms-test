<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Requests;

use App\Enums\RoomStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreRoomRequest extends FormRequest
{
    /**
     * Determine whether the user is authorized.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create rooms');
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'hotel_id' => ['required', 'integer', 'exists:hotels,id'],
            'number' => ['required', 'string', 'max:20'],
            'floor' => ['nullable', 'string', 'max:10'],
            'type' => ['required', 'string', 'max:50'],
            'status' => ['required', new Enum(RoomStatus::class)],
            'base_rate' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'hotel_id.required' => 'Hotel is required',
            'hotel_id.exists' => 'Selected hotel does not exist',
            'number.required' => 'Room number is required',
            'number.max' => 'Room number cannot exceed 20 characters',
            'floor.max' => 'Floor cannot exceed 10 characters',
            'type.required' => 'Room type is required',
            'type.max' => 'Room type cannot exceed 50 characters',
            'base_rate.required' => 'Base rate is required',
            'base_rate.numeric' => 'Base rate must be a number',
            'base_rate.min' => 'Base rate cannot be negative',
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
