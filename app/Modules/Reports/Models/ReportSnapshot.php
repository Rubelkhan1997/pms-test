<?php

declare(strict_types=1);

namespace App\Modules\Reports\Models;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportSnapshot extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'report_snapshots';

    protected $fillable = [
        'hotel_id',
        'created_by',
        'reference',
        'status',
        'report_type',
        'report_date',
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
            'report_date' => 'date',
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
     * Scope by report type.
     */
    public function scopeReportType(Builder $query, string $type): Builder
    {
        return $query->where('report_type', $type);
    }
}
