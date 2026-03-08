<?php

declare(strict_types=1);

namespace App\Modules\Hr\Actions;

use App\Modules\Hr\Models\Employee;

class CreateEmployeeAction
{
    /**
     * Persist a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function __invoke(array $payload): Employee
    {
        return Employee::query()->create($payload);
    }
}

