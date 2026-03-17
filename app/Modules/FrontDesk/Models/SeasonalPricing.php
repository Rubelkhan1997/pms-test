<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Seasonal Pricing Model
 */
class SeasonalPricing extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'hotel_id',
        'rate_plan_id',
        'name',
        'start_date',
        'end_date',
        'type',
        'adjustment',
        'is_active',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'adjustment' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
    
    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }
}
