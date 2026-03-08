<?php

declare(strict_types=1);

namespace App\Modules\Hr\Services;

use App\Modules\Hr\Actions\CreateEmployeeAction;
use App\Modules\Hr\Data\EmployeeData;
use App\Modules\Hr\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class EmployeeService
{
    /**
     * Create a new service instance.
     */
    public function __construct(private CreateEmployeeAction $createAction)
    {
    }

    /**
     * Return a paginated list.
     */
    public function paginate(): LengthAwarePaginator
    {
        return Employee::query()->latest('id')->paginate(15);
    }

    /**
     * Store a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): Employee
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

