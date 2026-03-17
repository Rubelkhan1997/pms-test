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
 * Class Payroll
 * 
 * @property int $id
 * @property int $hotel_id
 * @property int $employee_id
 * @property \Carbon\Carbon $period_start
 * @property \Carbon\Carbon $period_end
 * @property float $gross_amount
 * @property float $deduction_amount
 * @property float $net_amount
 * @property string $status
 * @property \Carbon\Carbon|null $paid_at
 */
class Payroll extends Model
{
    use HasFactory, HasHotel, SoftDeletes;
    
    protected $fillable = [
        'hotel_id',
        'employee_id',
        'period_start',
        'period_end',
        'gross_amount',
        'deduction_amount',
        'net_amount',
        'status',
        'paid_at',
        'meta',
    ];
    
    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'paid_at' => 'datetime',
        'gross_amount' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'meta' => 'array',
    ];
    
    /**
     * Get the employee for this payroll
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    
    /**
     * Get the hotel that owns this payroll
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}
