<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    /**
     * Determine whether the user is authorized.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'hotel_id' => ['nullable', 'integer', 'exists:hotels,id'],
            'guest_profile_id' => ['required', 'integer', 'exists:guest_profiles,id'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'adults' => ['nullable', 'integer', 'min:1', 'max:10'],
            'children' => ['nullable', 'integer', 'min:0', 'max:10'],
            'status' => ['required', 'string', 'in:pending,confirmed,checked_in,checked_out,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'guest_profile_id.required' => 'Please select a guest',
            'guest_profile_id.exists' => 'Selected guest does not exist',
            'room_id.required' => 'Please select a room',
            'room_id.exists' => 'Selected room does not exist',
            'check_in_date.required' => 'Check-in date is required',
            'check_in_date.after_or_equal' => 'Check-in date must be today or later',
            'check_out_date.required' => 'Check-out date is required',
            'check_out_date.after' => 'Check-out date must be after check-in date',
            'total_amount.required' => 'Total amount is required',
            'total_amount.min' => 'Total amount cannot be negative',
            'status.required' => 'Reservation status is required',
            'status.in' => 'Invalid status value',
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

