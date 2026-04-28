<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:200'],
            'domain'        => ['required', 'string', 'max:255', 'unique:landlord.tenants,domain'],
            'status'        => ['sometimes', 'in:pending,active,trial,suspended'],
            'contact_name'  => ['nullable', 'string', 'max:200'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'plan_id'       => ['nullable', 'integer', 'exists:landlord.subscription_plans,id'],
        ];
    }
}