<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['sometimes', 'string', 'max:200'],
            'contact_name'  => ['nullable', 'string', 'max:200'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'plan_id'       => ['nullable', 'integer', 'exists:landlord.subscription_plans,id'],
        ];
    }
}