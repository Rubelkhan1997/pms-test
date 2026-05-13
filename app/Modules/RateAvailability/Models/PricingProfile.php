<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Models;

use App\Modules\FrontDesk\Models\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingProfile extends Model
{
    use HasFactory;

    protected $table = 'pricing_profiles';

    protected $fillable = [
        'property_id', 'name', 'code', 'target_market', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function ratePlans(): HasMany
    {
        return $this->hasMany(RatePlan::class);
    }
}
