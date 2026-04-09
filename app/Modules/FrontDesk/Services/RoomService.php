<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Services;

use App\Modules\FrontDesk\Actions\CreateRoomAction;
use App\Modules\FrontDesk\Data\RoomData;
use App\Modules\FrontDesk\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

readonly class RoomService
{
    /**
     * Create a new service instance.
     */
    public function __construct(private CreateRoomAction $createAction)
    {
    }

    /**
     * Return a paginated list.
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $query = Room::query()
            ->with('hotel:id,name')
            ->latest('id');

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search): void {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('floor', 'like', "%{$search}%")
                    ->orWhereHas('hotel', function ($hotelQuery) use ($search): void {
                        $hotelQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['hotel_id'])) {
            $query->where('hotel_id', (int) $filters['hotel_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage, page: $page);
    }

    /**
     * Find a room by ID.
     */
    public function find(int $id): Room
    {
        return Room::query()
            ->with('hotel:id,name')
            ->findOrFail($id);
    }

    /**
     * Store a new record.
     */
    public function create(RoomData $payload): Room
    {
        return DB::transaction(function () use ($payload): Room {
            return ($this->createAction)($payload->toArray());
        });
    }

    /**
     * Update an existing record.
     */
    public function update(int $id, RoomData $payload): Room
    {
        return DB::transaction(function () use ($id, $payload): Room {
            $room = $this->find($id);
            $room->update($payload->toArray());

            return $room->refresh()->load('hotel:id,name');
        });
    }

    /**
     * Delete a room.
     */
    public function delete(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $room = $this->find($id);
            $room->delete();
        });
    }
}
