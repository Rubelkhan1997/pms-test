<?php

declare(strict_types=1);

namespace App\Modules\Mobile\Services;

use App\Modules\Mobile\Actions\CreateMobileTaskAction;
use App\Modules\Mobile\Data\MobileTaskData;
use App\Modules\Mobile\Models\MobileTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class MobileTaskService
{
    /**
     * Create a new service instance.
     */
    public function __construct(private CreateMobileTaskAction $createAction)
    {
    }

    /**
     * Return a paginated list.
     */
    public function paginate(): LengthAwarePaginator
    {
        return MobileTask::query()->latest('id')->paginate(15);
    }

    /**
     * Store a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): MobileTask
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

