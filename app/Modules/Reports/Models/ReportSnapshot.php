<?php

declare(strict_types=1);

namespace App\Modules\Reports\Models;

use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Models\User;
use App\Modules\FrontDesk\Models\Hotel;
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
            'status' => ReportStatus::class,
            'report_type' => ReportType::class,
            'report_date' => 'date:Y-m-d',
            'scheduled_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    /**
     * Get the hotel.
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the creator.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope by hotel.
     */
    public function scopeForHotel(Builder $query, int $hotelId): Builder
    {
        return $query->where('hotel_id', $hotelId);
    }

    /**
     * Scope by report type.
     */
    public function scopeOfType(Builder $query, ReportType|string $type): Builder
    {
        $value = $type instanceof ReportType ? $type->value : $type;

        return $query->where('report_type', $value);
    }

    /**
     * Scope by status.
     */
    public function scopeWithStatus(Builder $query, ReportStatus|string $status): Builder
    {
        $value = $status instanceof ReportStatus ? $status->value : $status;

        return $query->where('status', $value);
    }
}
