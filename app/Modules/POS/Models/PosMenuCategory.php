<?php

declare(strict_types=1);

namespace App\Modules\POS\Models;

use App\Modules\FrontDesk\Models\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosMenuCategory extends Model
{
    use HasFactory;

    protected $table = 'pos_menu_categories';

    protected $fillable = [
        'property_id', 'outlet_id', 'name', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active'  => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class, 'outlet_id');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(PosMenuItem::class, 'category_id');
    }
}
