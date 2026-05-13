<?php

declare(strict_types=1);

namespace App\Modules\Guest\Models;

use App\Modules\FrontDesk\Models\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'agents';

    protected $fillable = [
        'property_id', 'company_id', 'name', 'email', 'phone',
        'type', 'address', 'website', 'commission_rate', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'is_active'       => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class, 'agent_id');
    }
}
