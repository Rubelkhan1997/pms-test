<?php

declare(strict_types=1);

namespace App\Modules\POS\Models;

use App\Modules\FrontDesk\Models\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosOutlet extends Model
{
    use HasFactory;

    protected $table = 'pos_outlets';

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

    public function menuCategories(): HasMany
    {
        return $this->hasMany(PosMenuCategory::class, 'outlet_id');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(PosMenuItem::class, 'outlet_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(PosOrder::class, 'outlet_id');
    }
}
