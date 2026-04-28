<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:100'],
            'slug'             => ['required', 'string', 'max:100', 'unique:landlord.subscription_plans,slug'],
            'property_limit'   => ['required', 'integer', 'min:1'],
            'room_limit'       => ['required', 'integer', 'min:1'],
            'price_monthly'    => ['required', 'numeric', 'min:0'],
            'price_annual'     => ['required', 'numeric', 'min:0'],
            'trial_enabled'    => ['boolean'],
            'trial_days'       => ['required_if:trial_enabled,true', 'integer', 'min:1', 'max:365'],
            'modules_included' => ['nullable', 'array'],
            'modules_included.*' => ['string'],
            'is_active'        => ['boolean'],
        ];
    }
}
