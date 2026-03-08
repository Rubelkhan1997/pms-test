<?php

declare(strict_types=1);

namespace App\Modules\Mobile\Policies;

use App\Models\User;
use App\Modules\Mobile\Models\MobileTask;

class MobileTaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view mobile_tasks');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create mobile_tasks');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MobileTask $entity): bool
    {
        return $user->can('edit mobile_tasks');
    }
}

