<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Services;

use App\Base\BaseService;
use App\Modules\Housekeeping\Enums\HousekeepingStatus;
use App\Modules\Housekeeping\Models\HousekeepingTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class HousekeepingService extends BaseService
{
    public function __construct(
        private HousekeepingTask $model
    ) {
        parent::setModel($model);
    }

    /**
     * Paginate housekeeping tasks with filters.
     *
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->model->query()
            ->with(['room', 'hotel', 'createdBy', 'assignedTo'])
            ->applyFilters($filters)
            ->latest('scheduled_at')
            ->paginate(15);
    }

    /**
     * Find task by ID with relationships.
     */
    public function find(int $id, array $relations = ['room', 'hotel', 'createdBy', 'assignedTo']): ?HousekeepingTask
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Find task by ID or throw exception.
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id, array $relations = ['room', 'hotel', 'createdBy', 'assignedTo']): HousekeepingTask
    {
        return $this->model->with($relations)->findOrFail($id);
    }

    /**
     * Create housekeeping task with reference number.
     *
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): HousekeepingTask
    {
        $payload['reference'] = $this->generateReference();
        $payload['status'] = $payload['status'] ?? HousekeepingStatus::Pending->value;
        
        return $this->model->create($payload);
    }

    /**
     * Update housekeeping task.
     *
     * @param array<string, mixed> $payload
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $payload): HousekeepingTask
    {
        $task = $this->findOrFail($id);
        $task->update($payload);
        
        return $task->fresh(['room', 'hotel']);
    }

    /**
     * Delete housekeeping task.
     *
     * @throws ModelNotFoundException
     */
    public function delete(int $id): bool
    {
        $task = $this->findOrFail($id);
        return $task->delete();
    }

    /**
     * Update task status.
     *
     * @throws ModelNotFoundException
     */
    public function updateStatus(int $id, HousekeepingStatus $status): HousekeepingTask
    {
        $task = $this->findOrFail($id);
        
        $updateData = ['status' => $status->value];
        
        if ($status === HousekeepingStatus::Completed) {
            $updateData['completed_at'] = now();
        } elseif ($status === HousekeepingStatus::InProgress) {
            $updateData['started_at'] = now();
        }
        
        $task->update($updateData);
        
        return $task->fresh(['room', 'hotel']);
    }

    /**
     * Get tasks by room.
     *
     * @return Collection<int, HousekeepingTask>
     */
    public function getByRoom(int $roomId): Collection
    {
        return $this->model->where('room_id', $roomId)
            ->latest('scheduled_at')
            ->get();
    }

    /**
     * Get pending tasks for hotel.
     *
     * @return Collection<int, HousekeepingTask>
     */
    public function getPendingTasks(int $hotelId): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->where('status', HousekeepingStatus::Pending->value)
            ->latest('scheduled_at')
            ->get();
    }

    /**
     * Get today's tasks for hotel.
     *
     * @return Collection<int, HousekeepingTask>
     */
    public function getTodayTasks(int $hotelId): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->whereDate('scheduled_at', today())
            ->latest('scheduled_at')
            ->get();
    }

    /**
     * Get tasks by status.
     *
     * @return Collection<int, HousekeepingTask>
     */
    public function getByStatus(int $hotelId, HousekeepingStatus $status): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->where('status', $status->value)
            ->latest('scheduled_at')
            ->get();
    }

    /**
     * Get task statistics for hotel.
     *
     * @return array<string, int>
     */
    public function getStatistics(int $hotelId): array
    {
        $total = $this->model->where('hotel_id', $hotelId)->count();
        
        return [
            'total' => $total,
            'pending' => $this->getByStatus($hotelId, HousekeepingStatus::Pending)->count(),
            'in_progress' => $this->getByStatus($hotelId, HousekeepingStatus::InProgress)->count(),
            'completed' => $this->getByStatus($hotelId, HousekeepingStatus::Completed)->count(),
            'blocked' => $this->getByStatus($hotelId, HousekeepingStatus::Blocked)->count(),
        ];
    }

    /**
     * Generate unique reference number.
     */
    private function generateReference(): string
    {
        return 'HK-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
