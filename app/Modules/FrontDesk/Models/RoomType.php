<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'room_types';

    protected $fillable = [
        'property_id', 'name', 'code', 'type', 'description', 'floor',
        'max_occupancy', 'adult_occupancy', 'child_occupancy',
        'num_bedrooms', 'num_bathrooms', 'area_sqm',
        'bed_types', 'base_rate', 'amenities', 'gallery_paths', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'bed_types'     => 'array',
            'amenities'     => 'array',
            'gallery_paths' => 'array',
            'area_sqm'      => 'decimal:2',
            'base_rate'     => 'decimal:2',
            'is_active'     => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
