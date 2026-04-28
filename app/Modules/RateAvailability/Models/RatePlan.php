<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatePlan extends Model
{
    protected $table = 'rate_plans';

    protected $fillable = [
        'property_id', 'room_type_id', 'pricing_profile_id', 'cancellation_policy_id',
        'name', 'code', 'description', 'base_rate', 'extra_adult_rate', 'extra_child_rate',
        'weekend_factor', 'occupancy_factor', 'is_dynamic', 'is_direct', 'is_ota', 'is_active',
    ];

    protected $casts = [
        'base_rate' => 'decimal:2',
        'extra_adult_rate' => 'decimal:2',
        'extra_child_rate' => 'decimal:2',
        'weekend_factor' => 'decimal:2',
        'occupancy_factor' => 'decimal:2',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\FrontDesk\Models\Property::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\FrontDesk\Models\RoomType::class);
    }

    public function pricingProfile(): BelongsTo
    {
        return $this->belongsTo(PricingProfile::class);
    }

    public function cancellationPolicy(): BelongsTo
    {
        return $this->belongsTo(CancellationPolicy::class);
    }
}