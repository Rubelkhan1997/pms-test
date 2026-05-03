<?php

declare(strict_types=1);

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftSchedule extends Model
{
    use HasFactory;

    protected $table = 'shift_schedules';

    protected $fillable = [
        'employee_id', 'shift_date', 'start_time', 'end_time', 'status',
    ];

    protected function casts(): array
    {
        return [
            'shift_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
