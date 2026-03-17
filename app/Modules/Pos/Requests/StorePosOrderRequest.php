<?php

declare(strict_types=1);

namespace App\Modules\Pos\Requests;

use App\Modules\Pos\Enums\POSOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePosOrderRequest extends FormRequest
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
            'hotel_id' => ['required', 'exists:hotels,id'],
            'outlet' => ['required', 'string', 'max:50'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::in(array_column(POSOrderStatus::cases(), 'value'))],
            'guest_name' => ['nullable', 'string', 'max:255'],
            'room_number' => ['nullable', 'string', 'max:20'],
            'reservation_id' => ['nullable', 'exists:reservations,id'],
            'scheduled_at' => ['nullable', 'date'],
            'items' => ['nullable', 'array'],
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
            'total_amount.min' => 'Total amount cannot be negative',
            'outlet.required' => 'Outlet (Restaurant/Bar/Spa) is required',
        ];
    }
}
