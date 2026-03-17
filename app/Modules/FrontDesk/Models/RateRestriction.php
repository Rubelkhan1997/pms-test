<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Rate Restriction Model
 */
class RateRestriction extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'rate_plan_id',
        'start_date',
        'end_date',
        'restriction_type',
        'value',
        'is_active',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];
    
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
    
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
    
    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }
}
