<?php

declare(strict_types=1);

namespace App\Modules\Guest\Policies;

use App\Models\User;
use App\Modules\Guest\Models\GuestProfile;

class GuestProfilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view guest_profiles');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create guest_profiles');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GuestProfile $entity): bool
    {
        return $user->can('edit guest_profiles');
    }
}

