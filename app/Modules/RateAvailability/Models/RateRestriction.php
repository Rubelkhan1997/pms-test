<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Models;

use App\Modules\FrontDesk\Models\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RateRestriction extends Model
{
    use HasFactory;

    protected $table = 'rate_restrictions';

    protected $fillable = [
        'property_id', 'rate_plan_id', 'date',
        'min_stay', 'max_stay', 'min_rooms', 'max_rooms',
        'closed', 'closed_to_arrival', 'closed_to_departure',
        'rate_override', 'stop_sell_reason',
    ];

    protected function casts(): array
    {
        return [
            'date'                 => 'date',
            'closed'               => 'boolean',
            'closed_to_arrival'    => 'boolean',
            'closed_to_departure'  => 'boolean',
            'rate_override'        => 'decimal:2',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }
}
