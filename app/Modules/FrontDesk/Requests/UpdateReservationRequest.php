<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
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
            'guest_id' => ['sometimes', 'integer', 'exists:guest_profiles,id'],
            'room_id' => ['sometimes', 'integer', 'exists:rooms,id'],
            'check_in_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'check_out_date' => ['sometimes', 'date', 'after:check_in_date'],
            'total_amount' => ['sometimes', 'numeric', 'min:0'],
            'adults' => ['sometimes', 'integer', 'min:1', 'max:10'],
            'children' => ['sometimes', 'integer', 'min:0', 'max:10'],
            'status' => ['sometimes', 'string', 'in:pending,draft,confirmed,checked_in,checked_out,cancelled'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'guest_id.exists' => 'Selected guest does not exist',
            'room_id.exists' => 'Selected room does not exist',
            'check_in_date.after_or_equal' => 'Check-in date must be today or later',
            'check_out_date.after' => 'Check-out date must be after check-in date',
            'total_amount.min' => 'Total amount cannot be negative',
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
