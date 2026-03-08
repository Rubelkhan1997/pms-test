<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Models;

use App\Enums\MaintenanceStatus;
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
        'hotel_id',
        'room_id',
        'title',
        'description',
        'status',
        'reported_at',
        'resolved_at',
    ];

    /**
     * Get cast definitions.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => MaintenanceStatus::class,
            'reported_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Get associated room.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Scope open requests.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', MaintenanceStatus::Open->value);
    }
}


