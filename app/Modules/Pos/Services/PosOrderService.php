<?php

declare(strict_types=1);

namespace App\Modules\Pos\Services;

use App\Modules\Pos\Actions\CreatePosOrderAction;
use App\Modules\Pos\Data\PosOrderData;
use App\Modules\Pos\Models\PosOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class PosOrderService
{
    /**
     * Create a new service instance.
     */
    public function __construct(private CreatePosOrderAction $createAction)
    {
    }

    /**
     * Return a paginated list.
     */
    public function paginate(): LengthAwarePaginator
    {
        return PosOrder::query()->latest('id')->paginate(15);
    }

    /**
     * Store a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): PosOrder
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

