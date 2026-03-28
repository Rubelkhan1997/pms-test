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
    public function paginate(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $query = Reservation::query()
            ->with(['hotel', 'room', 'guest'])
            ->latest('id');

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['check_in_date'])) {
            $query->where('check_in_date', '>=', $filters['check_in_date']);
        }

        if (!empty($filters['check_out_date'])) {
            $query->where('check_out_date', '<=', $filters['check_out_date']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('guest', function ($guestQuery) use ($search) {
                        $guestQuery->where(function ($q2) use ($search) {
                            $q2->where('first_name', 'like', "%{$search}%")
                               ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    });
            });
        }

        return $query->paginate($perPage, page: $page);
    }

    /**
     * Find a reservation by ID.
     */
    public function find(int $id): ?Reservation
    {
        return Reservation::with(['hotel', 'guest', 'room'])->find($id);
    }

    /**
     * Store a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): Reservation
    {
        $reservation = ($this->createAction)($payload);
        return $reservation->load(['hotel', 'room', 'guest']); // ✅ Load relations after create
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

    /**
     * Update an existing reservation.
     *
     * @param array<string, mixed> $payload
     */
    public function update(int $id, array $payload): ?Reservation
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return null;
        }
        $reservation->update($payload);
        return $reservation->load(['hotel', 'room', 'guest']); // ✅ Load relations after update
    }

    /**
     * Delete a reservation.
     */
    public function delete(int $id): bool
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return false;
        }
        return $reservation->delete();
    }

    /**
     * Check in a guest.
     */
    public function checkIn(int $id): ?Reservation
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return null;
        }
        $reservation->update(['status' => 'checked_in']);
        return $reservation->load(['hotel', 'room', 'guest']); // ✅ Load relations
    }

    /**
     * Check out a guest.
     *
     * @param array<string, mixed> $paymentData
     */
    public function checkOut(int $id, array $paymentData = []): ?Reservation
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return null;
        }
        $reservation->update([
            'status' => 'checked_out',
            ...$paymentData
        ]);
        return $reservation->load(['hotel', 'room', 'guest']); // ✅ Load relations
    }

    /**
     * Cancel a reservation.
     */
    public function cancel(int $id): ?Reservation
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return null;
        }
        $reservation->update(['status' => 'cancelled']);
        return $reservation->load(['hotel', 'room', 'guest']); // ✅ Load relations
    }
}

