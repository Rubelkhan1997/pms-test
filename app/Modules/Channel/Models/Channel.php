<?php

declare(strict_types=1);

namespace App\Modules\Channel\Models;

use App\Modules\FrontDesk\Models\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Channel extends Model
{
    use HasFactory;

    protected $table = 'channels';

    protected $fillable = [
        'property_id', 'name', 'code', 'type', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(ChannelMapping::class);
    }

    public function otaSyncs(): HasMany
    {
        return $this->hasMany(OtaSync::class);
    }
}
