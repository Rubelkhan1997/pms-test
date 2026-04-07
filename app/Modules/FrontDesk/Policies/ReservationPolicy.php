<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Policies;

use App\Models\User;
use App\Modules\FrontDesk\Models\Reservation;

class ReservationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view reservations');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Reservation $entity): bool
    {
        return $user->can('view reservations');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create reservations');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reservation $entity): bool
    {
        return $user->can('edit reservations');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reservation $entity): bool
    {
        return $user->can('delete reservations');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Reservation $entity): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Reservation $entity): bool
    {
        return false;
    }
}
