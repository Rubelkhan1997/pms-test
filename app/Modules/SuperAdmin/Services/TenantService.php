<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Services;

use App\Models\Tenant;
use App\Modules\SuperAdmin\Actions\ActivateTenantAction;
use App\Modules\SuperAdmin\Actions\CreateTenantAction;
use App\Modules\SuperAdmin\Actions\SuspendTenantAction;
use App\Modules\SuperAdmin\Data\TenantData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class TenantService
{
    public function __construct(
        private CreateTenantAction   $createAction,
        private SuspendTenantAction  $suspendAction,
        private ActivateTenantAction $activateAction,
    ) {}

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Tenant::on('landlord')->withTrashed(false)->latest('id');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('domain', 'like', "%{$filters['search']}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function findOrFail(int $id): Tenant
    {
        return Tenant::on('landlord')->findOrFail($id);
    }

    public function create(TenantData $data): Tenant
    {
        return $this->createAction->execute($data);
    }

    public function update(Tenant $tenant, TenantData $data): Tenant
    {
        $tenant->update([
            'name'          => $data->name,
            'contact_name'  => $data->contact_name,
            'contact_email' => $data->contact_email,
            'contact_phone' => $data->contact_phone,
            'plan_id'       => $data->plan_id,
        ]);
        return $tenant->fresh();
    }

    public function suspend(Tenant $tenant): Tenant
    {
        return $this->suspendAction->execute($tenant);
    }

    public function activate(Tenant $tenant): Tenant
    {
        return $this->activateAction->execute($tenant);
    }
}