<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'timezone',
        'currency',
        'email',
        'phone',
        'address',
    ];

    protected function casts(): array
    {
        return [
            'timezone' => 'string',
            'currency' => 'string',
        ];
    }
}
