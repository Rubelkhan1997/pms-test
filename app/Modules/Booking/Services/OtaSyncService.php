<?php

declare(strict_types=1);

namespace App\Modules\Booking\Services;

use App\Modules\Booking\Actions\RunOtaSyncAction;
use App\Modules\Booking\Data\OtaSyncData;
use App\Modules\Booking\Models\OtaSync;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class OtaSyncService
{
    /**
     * Create a new service instance.
     */
    public function __construct(private RunOtaSyncAction $createAction)
    {
    }

    /**
     * Return a paginated list.
     */
    public function paginate(): LengthAwarePaginator
    {
        return OtaSync::query()->latest('id')->paginate(15);
    }

    /**
     * Store a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): OtaSync
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

