<?php

namespace App\Modules\[MODULE]\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// FILE: app/Modules/[MODULE]/Http/Requests/[MODEL]StoreRequest.php

class [MODEL]StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // [VALIDATION_RULES]
            // Example:
            // 'name' => 'required|string|max:255',
            // 'code' => 'required|string|max:50|unique:[TABLE],code',
            // 'email' => 'nullable|email|max:255',
            // 'phone' => 'nullable|string|max:20',
            // 'address' => 'nullable|string',
            // 'status' => 'required|in:active,inactive',
            // 'hotel_id' => 'required|exists:hotels,id',
            // [END_VALIDATION_RULES]
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
            // [CUSTOM_MESSAGES]
            // Example:
            // 'name.required' => 'The [FIELD] field is required.',
            // 'code.unique' => 'The [FIELD] must be unique.',
            // [END_CUSTOM_MESSAGES]
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
