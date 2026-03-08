<?php

declare(strict_types=1);

namespace App\Modules\Hr\Events;

use App\Modules\Hr\Models\Employee;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeCreated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public readonly Employee $entity)
    {
    }
}

