<?php

declare(strict_types=1);

namespace App\Modules\Pos\Models;

use App\Traits\HasHotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PosMenuItem
 * 
 * @property int $id
 * @property int $hotel_id
 * @property string $name
 * @property string|null $category
 * @property float $price
 * @property bool $is_active
 * @property string|null $description
 * @property array|null $meta
 */
class PosMenuItem extends Model
{
    use HasFactory, HasHotel, SoftDeletes;
    
    protected $fillable = [
        'hotel_id',
        'name',
        'category',
        'price',
        'is_active',
        'description',
        'meta',
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'meta' => 'array',
    ];
    
    /**
     * Get the hotel that owns this menu item
     */
    public function hotel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Hotel::class);
    }
    
    /**
     * Scope to active items only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope to specific category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
