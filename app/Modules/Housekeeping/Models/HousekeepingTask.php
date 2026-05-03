<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Models;

use App\Models\User;
use App\Modules\FrontDesk\Models\Property;
use App\Modules\FrontDesk\Models\Room;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HousekeepingTask extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'housekeeping_tasks';

    protected $fillable = [
        'property_id', 'room_id', 'assigned_to', 'created_by',
        'reference', 'type', 'priority', 'status',
        'scheduled_at', 'started_at', 'completed_at', 'inspected_at',
        'notes', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at'  => 'datetime',
            'started_at'    => 'datetime',
            'completed_at'  => 'datetime',
            'inspected_at'  => 'datetime',
            'meta'          => 'array',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
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
        return $query->where('status', 'pending');
    }
}
