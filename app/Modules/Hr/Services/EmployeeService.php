<?php

declare(strict_types=1);

namespace App\Modules\Hr\Services;

use App\Base\BaseService;
use App\Modules\Hr\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class EmployeeService extends BaseService
{
    public function __construct(
        private Employee $model
    ) {
        parent::setModel($model);
    }

    /**
     * Paginate employees with filters.
     *
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->model->query()
            ->with(['hotel', 'user', 'createdBy'])
            ->applyFilters($filters)
            ->latest('created_at')
            ->paginate(15);
    }

    /**
     * Find employee by ID with relationships.
     */
    public function find(int $id, array $relations = ['hotel', 'user', 'createdBy', 'attendances', 'payrolls', 'shiftSchedules']): ?Employee
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Find employee by ID or throw exception.
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id, array $relations = ['hotel', 'user', 'createdBy', 'attendances', 'payrolls', 'shiftSchedules']): Employee
    {
        return $this->model->with($relations)->findOrFail($id);
    }

    /**
     * Create employee with reference number.
     *
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): Employee
    {
        $payload['reference'] = $this->generateReference();
        $payload['status'] = $payload['status'] ?? 'active';
        
        return $this->model->create($payload);
    }

    /**
     * Update employee.
     *
     * @param array<string, mixed> $payload
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $payload): Employee
    {
        $employee = $this->findOrFail($id);
        $employee->update($payload);
        
        return $employee->fresh(['hotel', 'user']);
    }

    /**
     * Delete employee.
     *
     * @throws ModelNotFoundException
     */
    public function delete(int $id): bool
    {
        $employee = $this->findOrFail($id);
        return $employee->delete();
    }

    /**
     * Get employees by department.
     *
     * @return Collection<int, Employee>
     */
    public function getByDepartment(int $hotelId, string $department): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->where('department', $department)
            ->where('status', 'active')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get active employees for hotel.
     *
     * @return Collection<int, Employee>
     */
    public function getActive(int $hotelId): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->where('status', 'active')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get employee statistics.
     *
     * @return array<string, int>
     */
    public function getStatistics(int $hotelId): array
    {
        $total = $this->model->where('hotel_id', $hotelId)->count();
        $active = $this->model->where('hotel_id', $hotelId)->where('status', 'active')->count();
        
        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
        ];
    }

    /**
     * Get departments for hotel.
     *
     * @return array<string>
     */
    public function getDepartments(int $hotelId): array
    {
        return $this->model->where('hotel_id', $hotelId)
            ->distinct()
            ->pluck('department')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Generate unique reference number.
     */
    private function generateReference(): string
    {
        return 'EMP-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
