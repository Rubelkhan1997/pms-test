<?php

declare(strict_types=1);

namespace App\Modules\ChannelManager\Models;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OTA Sync Queue Model
 * 
 * Represents a pending sync job to an OTA.
 */
class OtaSyncQueue extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'hotel_id',
        'ota_provider_id',
        'sync_type',
        'status',
        'payload',
        'attempts',
        'available_at',
        'completed_at',
        'error_message',
    ];
    
    protected $casts = [
        'payload' => 'array',
        'available_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
    
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
    
    public function otaProvider(): BelongsTo
    {
        return $this->belongsTo(OtaProvider::class);
    }
    
    /**
     * Check if job is ready to process.
     */
    public function isReady(): bool
    {
        return $this->status === 'pending' && 
               (!$this->available_at || $this->available_at->isPast());
    }
    
    /**
     * Mark job as processing.
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }
    
    /**
     * Mark job as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
    
    /**
     * Mark job as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'attempts' => $this->attempts + 1,
        ]);
    }
}
