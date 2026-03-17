<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Requests;

use App\Modules\FrontDesk\Enums\ReservationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'room_id' => ['sometimes', 'required', 'exists:rooms,id'],
            'guest_profile_id' => ['sometimes', 'required', 'exists:guest_profiles,id'],
            'check_in_date' => ['sometimes', 'required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['sometimes', 'required', 'date', 'after:check_in_date'],
            'adults' => ['sometimes', 'required', 'integer', 'min:1', 'max:10'],
            'children' => ['nullable', 'integer', 'min:0'],
            'total_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::in(array_column(ReservationStatus::cases(), 'value'))],
            'meta' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'check_out_date.after' => 'Check-out date must be after check-in date',
            'adults.max' => 'Maximum 10 adults allowed per reservation',
            'children.min' => 'Number of children cannot be negative',
        ];
    }
}
