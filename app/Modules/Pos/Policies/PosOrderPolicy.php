<?php

declare(strict_types=1);

namespace App\Modules\Pos\Policies;

use App\Models\User;
use App\Modules\Pos\Models\PosOrder;

class PosOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view pos_orders');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create pos_orders');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PosOrder $entity): bool
    {
        return $user->can('edit pos_orders');
    }
}

