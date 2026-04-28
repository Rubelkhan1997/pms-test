<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingProfile extends Model
{
    protected $table = 'pricing_profiles';

    protected $fillable = [
        'property_id', 'name', 'code', 'target_market', 'is_active',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\FrontDesk\Models\Property::class);
    }
}