<?php

declare(strict_types=1);

namespace App\Modules\RateAvailability\Models;

use App\Modules\FrontDesk\Models\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChargeCode extends Model
{
    use HasFactory;

    protected $table = 'charge_codes';

    protected $fillable = [
        'property_id', 'code', 'name', 'type',
        'default_amount', 'tax_rate', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_amount' => 'decimal:2',
            'tax_rate'       => 'decimal:2',
            'is_active'      => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
