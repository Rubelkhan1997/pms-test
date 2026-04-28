<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Services;

use App\Models\SubscriptionPlan;
use App\Modules\SuperAdmin\Actions\CreateSubscriptionPlanAction;
use App\Modules\SuperAdmin\Data\SubscriptionPlanData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class SubscriptionPlanService
{
    public function __construct(
        private CreateSubscriptionPlanAction $createAction,
    ) {}

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return SubscriptionPlan::on('landlord')
            ->orderBy('price_monthly')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): SubscriptionPlan
    {
        return SubscriptionPlan::on('landlord')->findOrFail($id);
    }

    public function create(SubscriptionPlanData $data): SubscriptionPlan
    {
        return $this->createAction->execute($data);
    }

    public function update(SubscriptionPlan $plan, SubscriptionPlanData $data): SubscriptionPlan
    {
        $plan->update($data->toArray());
        return $plan->fresh();
    }

    public function delete(SubscriptionPlan $plan): void
    {
        $plan->delete();
    }
}
