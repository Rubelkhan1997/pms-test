<?php

declare(strict_types=1);

namespace App\Modules\Mobile\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMobileTaskJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly int $entityId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('SyncMobileTaskJob executed', ['id' => $this->entityId]);
    }
}

