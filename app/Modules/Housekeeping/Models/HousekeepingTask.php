<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Models;

use App\Enums\HousekeepingStatus;
use App\Models\Hotel;
use App\Models\User;
use App\Modules\FrontDesk\Models\Room;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HousekeepingTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'housekeeping_tasks';

    protected $fillable = [
        'hotel_id',
        'room_id',
        'created_by',
        'assigned_to',
        'reference',
        'task_type',
        'status',
        'priority',
        'description',
        'scheduled_at',
        'started_at',
        'completed_at',
        'meta',
    ];

    /**
     * Get cast definitions.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => HousekeepingStatus::class,
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    /**
     * Get hotel.
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get room.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get creator.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get assigned user.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope pending tasks.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', HousekeepingStatus::Pending->value);
    }

    /**
     * Scope today's tasks.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('scheduled_at', today());
    }

    /**
     * Scope by task type.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('task_type', $type);
    }
}
