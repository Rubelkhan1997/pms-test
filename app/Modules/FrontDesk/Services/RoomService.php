<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Services;

use App\Base\BaseService;
use App\Modules\FrontDesk\Enums\RoomStatus;
use App\Modules\FrontDesk\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class RoomService extends BaseService
{
    public function __construct(
        private Room $model
    ) {
        parent::setModel($model);
    }

    /**
     * Paginate rooms with filters.
     *
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->model->query()
            ->with(['hotel'])
            ->applyFilters($filters)
            ->latest('number')
            ->paginate(20);
    }

    /**
     * Find room by ID with relationships.
     */
    public function find(int $id, array $relations = ['hotel', 'reservations']): ?Room
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Find room by ID or throw exception.
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id, array $relations = ['hotel', 'reservations']): Room
    {
        return $this->model->with($relations)->findOrFail($id);
    }

    /**
     * Create room.
     *
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): Room
    {
        $payload['status'] = $payload['status'] ?? RoomStatus::Available->value;
        
        return $this->model->create($payload);
    }

    /**
     * Update room.
     *
     * @param array<string, mixed> $payload
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $payload): Room
    {
        $room = $this->findOrFail($id);
        $room->update($payload);
        
        return $room->fresh(['hotel']);
    }

    /**
     * Delete room.
     *
     * @throws ModelNotFoundException
     */
    public function delete(int $id): bool
    {
        $room = $this->findOrFail($id);
        return $room->delete();
    }

    /**
     * Get rooms by hotel.
     *
     * @return Collection<int, Room>
     */
    public function getByHotel(int $hotelId): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->with(['hotel'])
            ->orderBy('number')
            ->get();
    }

    /**
     * Get rooms by status.
     *
     * @return Collection<int, Room>
     */
    public function getByStatus(int $hotelId, RoomStatus $status): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->where('status', $status->value)
            ->orderBy('number')
            ->get();
    }

    /**
     * Get available rooms.
     *
     * @return Collection<int, Room>
     */
    public function getAvailable(int $hotelId): Collection
    {
        return $this->getByStatus($hotelId, RoomStatus::Available);
    }

    /**
     * Get rooms by type.
     *
     * @return Collection<int, Room>
     */
    public function getByType(int $hotelId, string $type): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->where('type', $type)
            ->orderBy('number')
            ->get();
    }

    /**
     * Get room types for a hotel.
     *
     * @return array<string>
     */
    public function getRoomTypes(int $hotelId): array
    {
        return $this->model->where('hotel_id', $hotelId)
            ->distinct()
            ->pluck('type')
            ->toArray();
    }

    /**
     * Update room status.
     *
     * @throws ModelNotFoundException
     */
    public function updateStatus(int $id, RoomStatus $status): Room
    {
        $room = $this->findOrFail($id);
        $room->update(['status' => $status->value]);
        
        return $room->fresh(['hotel']);
    }

    /**
     * Get room statistics.
     *
     * @return array<string, int>
     */
    public function getStatistics(int $hotelId): array
    {
        $total = $this->model->where('hotel_id', $hotelId)->count();
        
        return [
            'total' => $total,
            'available' => $this->getByStatus($hotelId, RoomStatus::Available)->count(),
            'occupied' => $this->getByStatus($hotelId, RoomStatus::Occupied)->count(),
            'dirty' => $this->getByStatus($hotelId, RoomStatus::Dirty)->count(),
            'out_of_order' => $this->getByStatus($hotelId, RoomStatus::OutOfOrder)->count(),
        ];
    }

    /**
     * Check if room number exists for hotel.
     */
    public function numberExists(string $number, int $hotelId, ?int $excludeId = null): bool
    {
        $query = $this->model->where('number', $number)
            ->where('hotel_id', $hotelId);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Get rooms grid for floor.
     *
     * @return Collection<int, Room>
     */
    public function getByFloor(int $hotelId, int $floor): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->where('floor', $floor)
            ->orderBy('number')
            ->get();
    }

    /**
     * Get all floors for hotel.
     *
     * @return array<int>
     */
    public function getFloors(int $hotelId): array
    {
        return $this->model->where('hotel_id', $hotelId)
            ->distinct()
            ->orderBy('floor')
            ->pluck('floor')
            ->map(fn ($floor) => (int) $floor)
            ->toArray();
    }
}
