<?php

declare(strict_types=1);

namespace App\Modules\Hr\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
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
            'hotel_id' => ['sometimes', 'required', 'exists:hotels,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'department' => ['sometimes', 'required', 'string', 'max:100'],
            'status' => ['sometimes', 'in:active,inactive,terminated'],
            'scheduled_at' => ['nullable', 'date'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
