<?php

declare(strict_types=1);

namespace App\Modules\Guest\Models;

use App\Modules\FrontDesk\Models\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'companies';

    protected $fillable = [
        'property_id', 'name', 'email', 'phone', 'address',
        'tax_id', 'credit_limit', 'contract_rate_discount', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit'             => 'decimal:2',
            'contract_rate_discount'   => 'decimal:2',
            'is_active'                => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }
}
