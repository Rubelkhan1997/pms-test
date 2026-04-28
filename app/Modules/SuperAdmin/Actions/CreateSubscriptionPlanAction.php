<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Actions;

use App\Models\SubscriptionPlan;
use App\Modules\SuperAdmin\Data\SubscriptionPlanData;

class CreateSubscriptionPlanAction
{
    public function execute(SubscriptionPlanData $data): SubscriptionPlan
    {
        return SubscriptionPlan::create($data->toArray());
    }
}
