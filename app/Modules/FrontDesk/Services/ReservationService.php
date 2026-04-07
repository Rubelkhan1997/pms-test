<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Services;

use App\Modules\FrontDesk\Actions\CreateReservationAction; 
use App\Modules\FrontDesk\Data\ReservationData;
use App\Modules\FrontDesk\Models\Reservation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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
    public function find(int $id): Reservation
    {
        return Reservation::with(['hotel', 'guest', 'room'])->findOrFail($id);
    }

    /**
     * Store a new record.
     */
    public function create(ReservationData $payload): Reservation
    {
        return DB::transaction(function () use ($payload): Reservation {
            $reservation = ($this->createAction)($payload->toArray());
            return $reservation->load(['hotel', 'room', 'guest']); // Load relations after create
        });
    }
    
    /**
     * Update an existing reservation.
     */
    public function update(int $id, ReservationData $payload): Reservation
    {
        return DB::transaction(function () use ($id, $payload): Reservation {
            $reservation = $this->find($id);
            $reservation->update($payload->toArray());
            return $reservation->load(['hotel', 'room', 'guest']);
        });
    }

    /**
     * Delete a reservation.
     */
    public function delete(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $reservation = $this->find($id);
            $reservation->delete();
        });
    }

    /**
     * Cancel a reservation.
     */
    public function cancel(int $id): Reservation
    { 
        return DB::transaction(function () use ($id): Reservation {
            $reservation = $this->find($id);
            $reservation->update(['status' => 'cancelled']);
            return $reservation->load(['hotel', 'room', 'guest']);
        });
    }
}
