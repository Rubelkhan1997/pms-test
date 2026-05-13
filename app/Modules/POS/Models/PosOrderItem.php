<?php

declare(strict_types=1);

namespace App\Modules\POS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosOrderItem extends Model
{
    use HasFactory;

    protected $table = 'pos_order_items';

    protected $fillable = [
        'order_id', 'menu_item_id', 'name', 'quantity',
        'unit_price', 'discount_amount', 'tax_rate', 'tax_amount', 'total_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity'        => 'decimal:2',
            'unit_price'      => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_rate'        => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'total_amount'    => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class, 'order_id');
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(PosMenuItem::class, 'menu_item_id');
    }
}
