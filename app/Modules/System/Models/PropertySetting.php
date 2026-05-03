<?php

declare(strict_types=1);

namespace App\Modules\System\Models;

use App\Modules\FrontDesk\Models\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertySetting extends Model
{
    use HasFactory;

    protected $table = 'property_settings';

    protected $fillable = [
        'property_id', 'group', 'key', 'value',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
