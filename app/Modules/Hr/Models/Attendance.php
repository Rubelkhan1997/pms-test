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
 * Class Attendance
 * 
 * @property int $id
 * @property int $hotel_id
 * @property int $employee_id
 * @property \Carbon\Carbon $attendance_date
 * @property \Carbon\Carbon|null $check_in
 * @property \Carbon\Carbon|null $check_out
 * @property string $status
 */
class Attendance extends Model
{
    use HasFactory, HasHotel, SoftDeletes;
    
    protected $fillable = [
        'hotel_id',
        'employee_id',
        'attendance_date',
        'check_in',
        'check_out',
        'status',
    ];
    
    protected $casts = [
        'attendance_date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];
    
    /**
     * Get the employee for this attendance
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    
    /**
     * Get the hotel that owns this attendance
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
    
    /**
     * Calculate hours worked
     */
    public function getHoursWorkedAttribute(): ?float
    {
        if (!$this->check_in || !$this->check_out) {
            return null;
        }
        
        return round($this->check_in->diffInHours($this->check_out), 2);
    }
}
