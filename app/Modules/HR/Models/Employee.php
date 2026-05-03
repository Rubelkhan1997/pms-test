<?php

declare(strict_types=1);

namespace App\Modules\HR\Models;

use App\Models\User;
use App\Modules\FrontDesk\Models\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'employees';

    protected $fillable = [
        'property_id', 'department_id', 'user_id', 'created_by',
        'reference', 'status',
        'first_name', 'last_name', 'email', 'phone',
        'job_title', 'position_level', 'gender',
        'date_of_birth', 'hire_date', 'termination_date',
        'national_id', 'emergency_contact_name', 'emergency_contact_phone',
        'salary_amount', 'salary_type', 'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth'    => 'date',
            'hire_date'        => 'date',
            'termination_date' => 'date',
            'salary_amount'    => 'decimal:2',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
