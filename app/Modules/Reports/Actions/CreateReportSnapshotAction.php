<?php

declare(strict_types=1);

namespace App\Modules\Reports\Actions;

use App\Modules\Reports\Models\ReportSnapshot;

class CreateReportSnapshotAction
{
    /**
     * Persist a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function __invoke(array $payload): ReportSnapshot
    {
        return ReportSnapshot::query()->create($payload);
    }
}

