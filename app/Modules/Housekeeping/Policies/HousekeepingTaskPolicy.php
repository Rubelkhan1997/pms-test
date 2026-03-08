<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Policies;

use App\Models\User;
use App\Modules\Housekeeping\Models\HousekeepingTask;

class HousekeepingTaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view housekeeping_tasks');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create housekeeping_tasks');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, HousekeepingTask $entity): bool
    {
        return $user->can('edit housekeeping_tasks');
    }
}

