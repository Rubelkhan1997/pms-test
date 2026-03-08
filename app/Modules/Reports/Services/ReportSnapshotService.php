<?php

declare(strict_types=1);

namespace App\Modules\Reports\Services;

use App\Modules\Reports\Actions\CreateReportSnapshotAction;
use App\Modules\Reports\Data\ReportSnapshotData;
use App\Modules\Reports\Models\ReportSnapshot;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class ReportSnapshotService
{
    /**
     * Create a new service instance.
     */
    public function __construct(private CreateReportSnapshotAction $createAction)
    {
    }

    /**
     * Return a paginated list.
     */
    public function paginate(): LengthAwarePaginator
    {
        return ReportSnapshot::query()->latest('id')->paginate(15);
    }

    /**
     * Store a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): ReportSnapshot
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

