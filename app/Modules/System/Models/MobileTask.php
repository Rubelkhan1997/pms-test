<?php

declare(strict_types=1);

namespace App\Modules\System\Models;

use App\Models\User;
use App\Modules\FrontDesk\Models\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MobileTask extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'mobile_tasks';

    protected $fillable = [
        'property_id', 'assigned_to', 'created_by',
        'reference', 'title', 'description',
        'type', 'priority', 'status',
        'scheduled_at', 'completed_at', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'meta'         => 'array',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'assigned']);
    }
}
