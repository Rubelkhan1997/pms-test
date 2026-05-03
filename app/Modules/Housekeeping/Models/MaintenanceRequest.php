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

class MaintenanceRequest extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'maintenance_requests';

    protected $fillable = [
        'property_id', 'room_id', 'assigned_to', 'created_by',
        'reference', 'title', 'description', 'category', 'priority', 'status',
        'reported_at', 'started_at', 'resolved_at', 'closed_at',
        'estimated_cost', 'actual_cost', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'reported_at'    => 'datetime',
            'started_at'     => 'datetime',
            'resolved_at'    => 'datetime',
            'closed_at'      => 'datetime',
            'estimated_cost' => 'decimal:2',
            'actual_cost'    => 'decimal:2',
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

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'in_progress', 'on_hold']);
    }
}
