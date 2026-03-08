<?php

declare(strict_types=1);

namespace App\Enums;

enum RoomStatus: string
{
    case Available = 'available';
    case Occupied = 'occupied';
    case Dirty = 'dirty';
    case OutOfOrder = 'out_of_order';
}
