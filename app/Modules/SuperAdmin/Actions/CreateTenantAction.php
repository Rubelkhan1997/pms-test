<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Actions;

use App\Models\Tenant;
use App\Modules\SuperAdmin\Data\TenantData;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class CreateTenantAction
{
    public function execute(TenantData $data): Tenant
    {
        $slug   = Str::slug($data->name);
        $dbName = 'pms_' . Str::replace('-', '_', $slug);

        $tenant = Tenant::create([
            'name'          => $data->name,
            'slug'          => $slug,
            'domain'        => $data->domain,
            'database'      => $dbName,
            'status'        => $data->status,
            'contact_name'  => $data->contact_name,
            'contact_email' => $data->contact_email,
            'contact_phone' => $data->contact_phone,
            'plan_id'       => $data->plan_id,
        ]);

        Artisan::call('tenant:create', [
            'name'   => $data->name,
            'domain' => $data->domain,
        ]);

        return $tenant;
    }
}