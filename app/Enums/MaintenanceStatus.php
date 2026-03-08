<?php

declare(strict_types=1);

namespace App\Enums;

enum MaintenanceStatus: string
{
    case Open = 'open';
    case Assigned = 'assigned';
    case Resolved = 'resolved';
    case Closed = 'closed';
}
