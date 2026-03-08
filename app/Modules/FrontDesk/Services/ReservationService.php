<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Services;

use App\Modules\FrontDesk\Actions\CreateReservationAction;
use App\Modules\FrontDesk\Data\ReservationData;
use App\Modules\FrontDesk\Models\Reservation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class ReservationService
{
    /**
     * Create a new service instance.
     */
    public function __construct(private CreateReservationAction $createAction)
    {
    }

    /**
     * Return a paginated list.
     */
    public function paginate(): LengthAwarePaginator
    {
        return Reservation::query()->latest('id')->paginate(15);
    }

    /**
     * Store a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): Reservation
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

