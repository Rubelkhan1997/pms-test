<?php

declare(strict_types=1);

namespace App\Enums;

enum ReservationStatus: string
{
    case Pending = 'pending';
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case CheckedIn = 'checked_in';
    case CheckedOut = 'checked_out';
    case Cancelled = 'cancelled';
}
