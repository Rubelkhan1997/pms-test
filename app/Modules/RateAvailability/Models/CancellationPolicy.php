<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CancellationPolicy extends Model
{
    protected $table = 'cancellation_policies';

    protected $fillable = [
        'property_id', 'name', 'deadline_days', 'deadline_type',
        'cancellation_charge_percent', 'no_show_charge_percent', 'description', 'is_default',
    ];

    protected $casts = [
        'cancellation_charge_percent' => 'decimal:2',
        'no_show_charge_percent' => 'decimal:2',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\FrontDesk\Models\Property::class);
    }
}