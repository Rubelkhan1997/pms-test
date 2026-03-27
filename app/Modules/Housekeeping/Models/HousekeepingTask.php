<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Models;

use App\Enums\HousekeepingStatus;
use App\Models\User;
use App\Modules\FrontDesk\Models\Hotel;
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
        'hotel_id',
        'room_id',
        'created_by',
        'reference',
        'status',
        'scheduled_at',
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
     * Scope open tasks.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            HousekeepingStatus::Pending->value,
            HousekeepingStatus::InProgress->value,
        ]);
    }
}
