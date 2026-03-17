<?php

declare(strict_types=1);

namespace App\Modules\Hr\Models;

use App\Models\Hotel;
use App\Traits\HasHotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ShiftSchedule
 * 
 * @property int $id
 * @property int $hotel_id
 * @property int $employee_id
 * @property \Carbon\Carbon $shift_date
 * @property string $start_time
 * @property string $end_time
 * @property string $status
 */
class ShiftSchedule extends Model
{
    use HasFactory, HasHotel, SoftDeletes;
    
    protected $fillable = [
        'hotel_id',
        'employee_id',
        'shift_date',
        'start_time',
        'end_time',
        'status',
    ];
    
    protected $casts = [
        'shift_date' => 'date',
    ];
    
    /**
     * Get the employee for this shift schedule
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    
    /**
     * Get the hotel that owns this shift schedule
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
    
    /**
     * Get shift duration in hours
     */
    public function getDurationHoursAttribute(): float
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);
        
        return $start->diffInHours($end);
    }
}
