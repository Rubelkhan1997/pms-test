<?php

declare(strict_types=1);

namespace App\Modules\Reports\Policies;

use App\Models\User;
use App\Modules\Reports\Models\ReportSnapshot;

class ReportSnapshotPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view report_snapshots');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create report_snapshots');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ReportSnapshot $entity): bool
    {
        return $user->can('edit report_snapshots');
    }
}

