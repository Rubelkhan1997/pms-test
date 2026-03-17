<?php

declare(strict_types=1);

namespace App\Modules\ChannelManager\Models;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OTA Sync Log Model
 * 
 * Audit trail for all OTA sync operations.
 */
class OtaSyncLog extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'hotel_id',
        'ota_provider_id',
        'connection_id',
        'sync_type',
        'direction',
        'success',
        'request_data',
        'response_data',
        'response_code',
        'error_message',
        'execution_time',
        'synced_at',
    ];
    
    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'success' => 'boolean',
        'execution_time' => 'decimal:3',
        'synced_at' => 'datetime',
    ];
    
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
    
    public function otaProvider(): BelongsTo
    {
        return $this->belongsTo(OtaProvider::class);
    }
    
    public function connection(): BelongsTo
    {
        return $this->belongsTo(HotelOtaConnection::class, 'connection_id');
    }
}
