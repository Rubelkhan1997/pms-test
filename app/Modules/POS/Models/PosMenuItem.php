<?php

declare(strict_types=1);

namespace App\Modules\POS\Models;

use App\Modules\FrontDesk\Models\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosMenuItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pos_menu_items';

    protected $fillable = [
        'property_id', 'outlet_id', 'category_id',
        'name', 'code', 'description', 'image_path',
        'price', 'tax_rate', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price'     => 'decimal:2',
            'tax_rate'  => 'decimal:2',
            'is_active' => 'boolean',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(PosMenuCategory::class, 'category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
