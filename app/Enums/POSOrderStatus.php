<?php

declare(strict_types=1);

namespace App\Enums;

enum POSOrderStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Served = 'served';
    case Settled = 'settled';
    case Cancelled = 'cancelled';
}
