<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Requests;

use App\Modules\FrontDesk\Enums\RoomStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoomRequest extends FormRequest
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
            'number' => ['required', 'string', 'max:20'],
            'floor' => ['nullable', 'integer', 'min:0'],
            'type' => ['required', 'string', 'max:50'],
            'status' => ['sometimes', Rule::in(array_column(RoomStatus::cases(), 'value'))],
            'base_rate' => ['required', 'numeric', 'min:0'],
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
            'number.unique' => 'Room number already exists for this hotel',
            'base_rate.min' => 'Base rate cannot be negative',
        ];
    }
}
