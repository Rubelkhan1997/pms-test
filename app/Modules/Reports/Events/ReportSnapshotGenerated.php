<?php

declare(strict_types=1);

namespace App\Modules\Reports\Events;

use App\Modules\Reports\Models\ReportSnapshot;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportSnapshotGenerated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public readonly ReportSnapshot $entity)
    {
    }
}

