<?php

declare(strict_types=1);

namespace App\Modules\Auth\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case STAFF = 'staff';
    case MANAGER = 'manager';

    /**
     * Get all role values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if value is a valid role
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }
}
