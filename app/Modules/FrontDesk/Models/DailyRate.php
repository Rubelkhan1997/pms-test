<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Daily Rate Model
 */
class DailyRate extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'rate_plan_id',
        'date',
        'rate',
        'inventory',
        'is_available',
    ];
    
    protected $casts = [
        'date' => 'date',
        'rate' => 'decimal:2',
        'is_available' => 'boolean',
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
