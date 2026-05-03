<?php

declare(strict_types=1);

namespace App\Modules\Channel\Models;

use App\Modules\FrontDesk\Models\RoomType;
use App\Modules\RateAvailability\Models\RatePlan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChannelMapping extends Model
{
    use HasFactory;

    protected $table = 'channel_mappings';

    protected $fillable = [
        'channel_id', 'room_type_id', 'rate_plan_id',
        'channel_room_id', 'channel_rate_id', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }
}
