<?php

declare(strict_types=1);

namespace App\Modules\Reports\Observers;

use App\Modules\Reports\Events\ReportSnapshotGenerated;
use App\Modules\Reports\Models\ReportSnapshot;

class ReportSnapshotObserver
{
    /**
     * Handle the model created event.
     */
    public function created(ReportSnapshot $entity): void
    {
        ReportSnapshotGenerated::dispatch($entity);
    }
}

