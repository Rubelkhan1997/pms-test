<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $connection = 'landlord';

    protected $fillable = [
        'name', 'slug', 'property_limit', 'room_limit',
        'price_monthly', 'price_annual',
        'trial_enabled', 'trial_days',
        'modules_included', 'is_active',
    ];

    protected $casts = [
        'modules_included' => 'array',
        'trial_enabled'    => 'boolean',
        'is_active'        => 'boolean',
        'price_monthly'     => 'decimal:2',
        'price_annual'     => 'decimal:2',
    ];
}
