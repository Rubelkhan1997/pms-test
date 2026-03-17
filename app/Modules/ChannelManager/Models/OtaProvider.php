<?php

declare(strict_types=1);

namespace App\Modules\ChannelManager\Models;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * OTA Provider Model
 * 
 * Represents an OTA/Channel provider (Booking.com, Expedia, etc.)
 */
class OtaProvider extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'code',
        'api_type',
        'api_url',
        'credentials',
        'is_active',
        'supports_push',
        'supports_pull',
        'configuration',
    ];
    
    protected $casts = [
        'credentials' => 'encrypted:array',
        'configuration' => 'array',
        'is_active' => 'boolean',
        'supports_push' => 'boolean',
        'supports_pull' => 'boolean',
    ];
    
    /**
     * Get hotel connections for this provider.
     */
    public function connections(): HasMany
    {
        return $this->hasMany(HotelOtaConnection::class);
    }
    
    /**
     * Get room mappings for this provider.
     */
    public function roomMappings(): HasMany
    {
        return $this->hasMany(OtaRoomMapping::class);
    }
    
    /**
     * Get rate mappings for this provider.
     */
    public function rateMappings(): HasMany
    {
        return $this->hasMany(OtaRateMapping::class);
    }
    
    /**
     * Get sync queue items for this provider.
     */
    public function syncQueue(): HasMany
    {
        return $this->hasMany(OtaSyncQueue::class);
    }
    
    /**
     * Get sync logs for this provider.
     */
    public function syncLogs(): HasMany
    {
        return $this->hasMany(OtaSyncLog::class);
    }
    
    /**
     * Get OTA reservations for this provider.
     */
    public function otaReservations(): HasMany
    {
        return $this->hasMany(OtaReservation::class);
    }
    
    /**
     * Check if provider is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
    
    /**
     * Get provider display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return match ($this->code) {
            'booking' => 'Booking.com',
            'expedia' => 'Expedia',
            'agoda' => 'Agoda',
            'airbnb' => 'Airbnb',
            default => $this->name,
        };
    }
}
