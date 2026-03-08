<?php

declare(strict_types=1);

namespace App\Modules\Hr\Policies;

use App\Models\User;
use App\Modules\Hr\Models\Employee;

class EmployeePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view employees');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create employees');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Employee $entity): bool
    {
        return $user->can('edit employees');
    }
}

