<?php

declare(strict_types=1);

namespace App\Enums;

enum ReportType: string
{
    case Occupancy = 'occupancy';
    case Revenue = 'revenue';
    case Reservation = 'reservation';
    case Cashier = 'cashier';
    case Housekeeping = 'housekeeping';
    case Guest = 'guest';
    case NightAudit = 'night_audit';
}
