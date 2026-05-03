<?php

declare(strict_types=1);

namespace App\Modules\Channel\Models;

use App\Models\User;
use App\Modules\FrontDesk\Models\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtaSync extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ota_syncs';

    protected $fillable = [
        'property_id', 'channel_id', 'created_by',
        'reference', 'type', 'status',
        'sync_from', 'sync_to',
        'records_sent', 'records_updated', 'errors_count',
        'error_log', 'started_at', 'completed_at', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'sync_from'    => 'date',
            'sync_to'      => 'date',
            'error_log'    => 'array',
            'meta'         => 'array',
            'started_at'   => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
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
