<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Models;

use App\Modules\FrontDesk\Models\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CancellationPolicy extends Model
{
    use HasFactory;

    protected $table = 'cancellation_policies';

    protected $fillable = [
        'property_id', 'name', 'deadline_days', 'deadline_type',
        'cancellation_charge_percent', 'no_show_charge_percent',
        'description', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'cancellation_charge_percent' => 'decimal:2',
            'no_show_charge_percent'      => 'decimal:2',
            'is_default'                  => 'boolean',
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
