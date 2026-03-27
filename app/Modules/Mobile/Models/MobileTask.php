<?php

declare(strict_types=1);

namespace App\Modules\Mobile\Models;

use App\Models\User;
use App\Modules\FrontDesk\Models\Hotel;
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
        'hotel_id',
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
     * Get creator.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope pending items.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }
}
