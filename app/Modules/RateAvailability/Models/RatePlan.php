<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Models;

use App\Modules\FrontDesk\Models\Property;
use App\Modules\FrontDesk\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RatePlan extends Model
{
    use HasFactory;

    protected $table = 'rate_plans';

    protected $fillable = [
        'property_id', 'room_type_id', 'pricing_profile_id',
        'cancellation_policy_id', 'meal_plan_id',
        'name', 'code', 'description',
        'valid_from', 'valid_to',
        'base_rate', 'extra_adult_rate', 'extra_child_rate',
        'weekend_factor', 'occupancy_factor',
        'is_dynamic', 'is_direct', 'is_ota', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'valid_from'       => 'date',
            'valid_to'         => 'date',
            'base_rate'        => 'decimal:2',
            'extra_adult_rate' => 'decimal:2',
            'extra_child_rate' => 'decimal:2',
            'weekend_factor'   => 'decimal:2',
            'occupancy_factor' => 'decimal:2',
            'is_dynamic'       => 'boolean',
            'is_direct'        => 'boolean',
            'is_ota'           => 'boolean',
            'is_active'        => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function pricingProfile(): BelongsTo
    {
        return $this->belongsTo(PricingProfile::class);
    }

    public function cancellationPolicy(): BelongsTo
    {
        return $this->belongsTo(CancellationPolicy::class);
    }

    public function mealPlan(): BelongsTo
    {
        return $this->belongsTo(MealPlan::class);
    }

    public function restrictions(): HasMany
    {
        return $this->hasMany(RateRestriction::class);
    }
}
