<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Requests;

use App\Modules\FrontDesk\Enums\RoomStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends FormRequest
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
        $roomId = $this->route('id');
        
        return [
            'hotel_id' => ['sometimes', 'required', 'exists:hotels,id'],
            'number' => ['sometimes', 'required', 'string', 'max:20'],
            'floor' => ['nullable', 'integer', 'min:0'],
            'type' => ['sometimes', 'required', 'string', 'max:50'],
            'status' => ['sometimes', Rule::in(array_column(RoomStatus::cases(), 'value'))],
            'base_rate' => ['sometimes', 'required', 'numeric', 'min:0'],
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
