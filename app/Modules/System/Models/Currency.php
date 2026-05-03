<?php

declare(strict_types=1);

namespace App\Modules\System\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'currencies';

    protected $fillable = [
        'code', 'name', 'symbol', 'flag',
        'conversion_rate', 'is_active', 'is_default',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'conversion_rate' => 'decimal:6',
            'is_active'       => 'boolean',
            'is_default'      => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
