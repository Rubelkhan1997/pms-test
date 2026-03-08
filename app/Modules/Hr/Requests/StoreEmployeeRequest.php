<?php

declare(strict_types=1);

namespace App\Modules\Hr\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
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
            'hotel_id' => ['required', 'integer'],
            'reference' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', 'max:50'],
            'scheduled_at' => ['nullable', 'date'],
            'meta' => ['nullable', 'array'],
        ];
    }
}

