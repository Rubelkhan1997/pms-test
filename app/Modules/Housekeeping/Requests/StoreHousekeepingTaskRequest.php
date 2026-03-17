<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Requests;

use App\Modules\Housekeeping\Enums\HousekeepingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHousekeepingTaskRequest extends FormRequest
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
            'room_id' => ['required', 'exists:rooms,id'],
            'task_type' => ['required', 'in:cleaning,maintenance,inspection,delivery'],
            'status' => ['sometimes', Rule::in(array_column(HousekeepingStatus::cases(), 'value'))],
            'priority' => ['sometimes', 'in:low,normal,high,urgent'],
            'scheduled_at' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'description' => ['nullable', 'string', 'max:1000'],
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
            'room_id.exists' => 'The selected room does not exist.',
            'task_type.in' => 'Invalid task type. Must be cleaning, maintenance, inspection, or delivery.',
        ];
    }
}
