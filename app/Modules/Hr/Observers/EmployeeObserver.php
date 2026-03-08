<?php

declare(strict_types=1);

namespace App\Modules\Hr\Observers;

use App\Modules\Hr\Events\EmployeeCreated;
use App\Modules\Hr\Models\Employee;

class EmployeeObserver
{
    /**
     * Handle the model created event.
     */
    public function created(Employee $entity): void
    {
        EmployeeCreated::dispatch($entity);
    }
}

