<?php

declare(strict_types=1);

namespace App\Modules\ChannelManager\Models;

use App\Models\Hotel;
use App\Modules\FrontDesk\Models\RatePlan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OTA Rate Mapping Model
 */
class OtaRateMapping extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'hotel_id',
        'rate_plan_id',
        'ota_provider_id',
        'ota_rate_plan_id',
        'rate_plan_name_ota',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
    
    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }
    
    public function otaProvider(): BelongsTo
    {
        return $this->belongsTo(OtaProvider::class);
    }
}
