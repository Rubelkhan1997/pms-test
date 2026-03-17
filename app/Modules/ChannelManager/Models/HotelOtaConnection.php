<?php

declare(strict_types=1);

namespace App\Modules\ChannelManager\Models;

use App\Models\Hotel;
use App\Modules\ChannelManager\Models\OtaProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Hotel OTA Connection Model
 * 
 * Represents a hotel's connection to an OTA provider.
 */
class HotelOtaConnection extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'hotel_id',
        'ota_provider_id',
        'property_id',
        'hotel_id_ota',
        'credentials',
        'status',
        'last_sync_at',
        'last_error_at',
        'last_error_message',
        'sync_retry_count',
        'settings',
    ];
    
    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'last_sync_at' => 'datetime',
        'last_error_at' => 'datetime',
    ];
    
    /**
     * Get the hotel.
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
    
    /**
     * Get the OTA provider.
     */
    public function otaProvider(): BelongsTo
    {
        return $this->belongsTo(OtaProvider::class);
    }
    
    /**
     * Get room mappings for this connection.
     */
    public function roomMappings(): HasMany
    {
        return $this->hasMany(OtaRoomMapping::class, 'hotel_id', 'hotel_id')
            ->where('ota_provider_id', $this->ota_provider_id);
    }
    
    /**
     * Get sync queue items for this connection.
     */
    public function syncQueue(): HasMany
    {
        return $this->hasMany(OtaSyncQueue::class, 'hotel_id', 'hotel_id')
            ->where('ota_provider_id', $this->ota_provider_id);
    }
    
    /**
     * Get sync logs for this connection.
     */
    public function syncLogs(): HasMany
    {
        return $this->hasMany(OtaSyncLog::class, 'connection_id');
    }
    
    /**
     * Check if connection is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    
    /**
     * Check if connection has errors.
     */
    public function hasErrors(): bool
    {
        return $this->status === 'error' || $this->status === 'suspended';
    }
    
    /**
     * Record successful sync.
     */
    public function recordSuccess(): void
    {
        $this->update([
            'status' => 'active',
            'last_sync_at' => now(),
            'sync_retry_count' => 0,
        ]);
    }
    
    /**
     * Record failed sync.
     */
    public function recordError(string $errorMessage): void
    {
        $this->update([
            'status' => 'error',
            'last_error_at' => now(),
            'last_error_message' => $errorMessage,
            'sync_retry_count' => $this->sync_retry_count + 1,
        ]);
    }
    
    /**
     * Get pending sync jobs count.
     */
    public function getPendingSyncsCountAttribute(): int
    {
        return $this->syncQueue()
            ->where('status', 'pending')
            ->count();
    }
}
