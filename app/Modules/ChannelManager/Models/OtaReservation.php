<?php

declare(strict_types=1);

namespace App\Modules\ChannelManager\Models;

use App\Models\Hotel;
use App\Modules\FrontDesk\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OTA Reservation Model
 * 
 * Reservations imported from OTAs.
 */
class OtaReservation extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'hotel_id',
        'reservation_id',
        'ota_provider_id',
        'ota_reservation_id',
        'confirmation_number',
        'guest_name',
        'guest_email',
        'guest_phone',
        'check_in_date',
        'check_out_date',
        'adults',
        'children',
        'rooms',
        'total_amount',
        'currency',
        'status',
        'raw_data',
        'imported_data',
        'is_imported',
        'imported_at',
    ];
    
    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'total_amount' => 'decimal:2',
        'raw_data' => 'array',
        'imported_data' => 'array',
        'is_imported' => 'boolean',
        'imported_at' => 'datetime',
    ];
    
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
    
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
    
    public function otaProvider(): BelongsTo
    {
        return $this->belongsTo(OtaProvider::class);
    }
    
    /**
     * Check if reservation is imported to PMS.
     */
    public function isImported(): bool
    {
        return $this->is_imported;
    }
    
    /**
     * Mark as imported.
     */
    public function markAsImported(Reservation $reservation): void
    {
        $this->update([
            'reservation_id' => $reservation->id,
            'is_imported' => true,
            'imported_at' => now(),
            'imported_data' => $reservation->only([
                'reference',
                'status',
                'check_in_date',
                'check_out_date',
                'adults',
                'children',
                'total_amount',
            ]),
        ]);
    }
}
