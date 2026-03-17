<?php

declare(strict_types=1);

namespace App\Modules\Hr\Models;

use App\Models\User;
use App\Traits\HasHotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Employee
 * 
 * @property int $id
 * @property int $hotel_id
 * @property int|null $user_id
 * @property int|null $created_by
 * @property string $reference
 * @property string $status
 * @property string|null $department
 * @property \Carbon\Carbon|null $scheduled_at
 * @property array|null $meta
 */
class Employee extends Model
{
    use HasFactory, HasHotel, SoftDeletes;
    
    protected $fillable = [
        'hotel_id',
        'user_id',
        'created_by',
        'reference',
        'status',
        'department',
        'scheduled_at',
        'meta',
    ];
    
    protected $casts = [
        'scheduled_at' => 'datetime',
        'meta' => 'array',
    ];
    
    /**
     * Get the hotel that owns this employee
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
    
    /**
     * Get the user associated with this employee
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the creator of this employee record
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get attendance records for this employee
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
    
    /**
     * Get payroll records for this employee
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }
    
    /**
     * Get shift schedules for this employee
     */
    public function shiftSchedules(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class);
    }
}
