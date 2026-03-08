<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Services;

use App\Modules\Housekeeping\Actions\CreateHousekeepingTaskAction;
use App\Modules\Housekeeping\Data\HousekeepingTaskData;
use App\Modules\Housekeeping\Models\HousekeepingTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class HousekeepingTaskService
{
    /**
     * Create a new service instance.
     */
    public function __construct(private CreateHousekeepingTaskAction $createAction)
    {
    }

    /**
     * Return a paginated list.
     */
    public function paginate(): LengthAwarePaginator
    {
        return HousekeepingTask::query()->latest('id')->paginate(15);
    }

    /**
     * Store a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): HousekeepingTask
    {
        return ($this->createAction)($payload);
    }

    /**
     * Return a UI badge color by status.
     */
    public function statusBadge(string $status): string
    {
        return match ($status) {
            'confirmed', 'paid', 'completed', 'served', 'resolved' => 'success',
            'cancelled', 'failed', 'blocked' => 'danger',
            default => 'warning',
        };
    }
}

