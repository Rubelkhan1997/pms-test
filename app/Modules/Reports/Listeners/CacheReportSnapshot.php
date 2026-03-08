<?php

declare(strict_types=1);

namespace App\Modules\Reports\Listeners;

use App\Modules\Reports\Events\ReportSnapshotGenerated;
use Illuminate\Support\Facades\Log;

class CacheReportSnapshot
{
    /**
     * Handle the event.
     */
    public function handle(ReportSnapshotGenerated $event): void
    {
        Log::info('ReportSnapshotGenerated handled', ['id' => $event->entity->id]);
    }
}

