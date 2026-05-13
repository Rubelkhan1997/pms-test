<?php

declare(strict_types=1);

namespace App\Modules\Reports\Models;

use App\Models\User;
use App\Modules\FrontDesk\Models\Property;
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
        'property_id', 'created_by', 'reference',
        'report_type', 'report_date', 'status', 'data',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'data'        => 'array',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('report_type', $type);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
