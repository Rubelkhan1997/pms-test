<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Services;

use App\Base\BaseService;
use App\Modules\FrontDesk\Actions\CreateReservationAction;
use App\Modules\FrontDesk\Data\ReservationData;
use App\Modules\FrontDesk\Enums\ReservationStatus;
use App\Modules\FrontDesk\Models\Reservation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class ReservationService extends BaseService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private CreateReservationAction $createAction,
        private Reservation $model
    ) {
        parent::setModel($model);
    }

    /**
     * Return a paginated list with filters.
     *
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->model->query()
            ->with(['room', 'guestProfile', 'createdBy'])
            ->applyFilters($filters)
            ->latest('id')
            ->paginate(15);
    }

    /**
     * Find a reservation by ID with relationships.
     */
    public function find(int $id, array $relations = ['room', 'guestProfile', 'createdBy']): ?Reservation
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Find a reservation by ID or throw exception.
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id, array $relations = ['room', 'guestProfile', 'createdBy']): Reservation
    {
        return $this->model->with($relations)->findOrFail($id);
    }

    /**
     * Store a new reservation with reference number.
     *
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): Reservation
    {
        return ($this->createAction)($payload);
    }

    /**
     * Update an existing reservation.
     *
     * @param array<string, mixed> $payload
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $payload): Reservation
    {
        $reservation = $this->findOrFail($id);
        $reservation->update($payload);

        return $reservation->fresh(['room', 'guestProfile', 'createdBy']);
    }

    /**
     * Delete a reservation (soft delete).
     *
     * @throws ModelNotFoundException
     */
    public function delete(int $id): bool
    {
        $reservation = $this->findOrFail($id);
        return $reservation->delete();
    }

    /**
     * Check in a reservation.
     *
     * @throws \InvalidArgumentException
     */
    public function checkIn(int $id): Reservation
    {
        $reservation = $this->findOrFail($id);

        if ($reservation->status !== ReservationStatus::Confirmed) {
            throw new \InvalidArgumentException('Only confirmed reservations can be checked in');
        }

        $reservation->update([
            'status' => ReservationStatus::CheckedIn->value,
            'actual_check_in' => now(),
        ]);

        // Update room status
        $reservation->room->update(['status' => 'occupied']);

        return $reservation->fresh(['room', 'guestProfile']);
    }

    /**
     * Check out a reservation.
     *
     * @throws \InvalidArgumentException
     */
    public function checkOut(int $id): Reservation
    {
        $reservation = $this->findOrFail($id);

        if ($reservation->status !== ReservationStatus::CheckedIn) {
            throw new \InvalidArgumentException('Only checked-in reservations can be checked out');
        }

        $reservation->update([
            'status' => ReservationStatus::CheckedOut->value,
            'actual_check_out' => now(),
        ]);

        // Update room status to dirty (needs cleaning)
        $reservation->room->update(['status' => 'dirty']);

        return $reservation->fresh(['room', 'guestProfile']);
    }

    /**
     * Cancel a reservation.
     *
     * @throws \InvalidArgumentException
     */
    public function cancel(int $id): Reservation
    {
        $reservation = $this->findOrFail($id);

        if (!in_array($reservation->status, [ReservationStatus::Draft, ReservationStatus::Confirmed], true)) {
            throw new \InvalidArgumentException('Only draft or confirmed reservations can be cancelled');
        }

        $reservation->update([
            'status' => ReservationStatus::Cancelled->value,
        ]);

        return $reservation->fresh();
    }

    /**
     * Get reservations by status.
     *
     * @return Collection<int, Reservation>
     */
    public function getByStatus(ReservationStatus $status): Collection
    {
        return $this->model->where('status', $status->value)
            ->with(['room', 'guestProfile'])
            ->get();
    }

    /**
     * Get today's arrivals.
     *
     * @return Collection<int, Reservation>
     */
    public function getTodayArrivals(): Collection
    {
        return $this->model->where('check_in_date', today())
            ->whereIn('status', [ReservationStatus::Confirmed->value, ReservationStatus::CheckedIn->value])
            ->with(['room', 'guestProfile'])
            ->get();
    }

    /**
     * Get today's departures.
     *
     * @return Collection<int, Reservation>
     */
    public function getTodayDepartures(): Collection
    {
        return $this->model->where('check_out_date', today())
            ->where('status', ReservationStatus::CheckedIn->value)
            ->with(['room', 'guestProfile'])
            ->get();
    }

    /**
     * Get in-house guests.
     *
     * @return Collection<int, Reservation>
     */
    public function getInHouse(): Collection
    {
        return $this->model->where('status', ReservationStatus::CheckedIn->value)
            ->with(['room', 'guestProfile'])
            ->get();
    }

    /**
     * Calculate balance.
     */
    public function calculateBalance(Reservation $reservation): float
    {
        return $reservation->total_amount - ($reservation->paid_amount ?? 0);
    }
}
