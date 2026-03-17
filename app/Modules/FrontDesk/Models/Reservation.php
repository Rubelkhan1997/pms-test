<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use App\Enums\ReservationStatus;
use App\Models\Hotel;
use App\Models\User;
use App\Modules\Guest\Models\GuestProfile;
use App\Traits\HasHotel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, HasHotel, SoftDeletes;

    protected $table = 'reservations';

    protected $fillable = [
        'hotel_id',
        'room_id',
        'room_type_id',
        'guest_profile_id',
        'rate_plan_id',
        'created_by',
        'reference',
        'channel',
        'market_segment',
        'source',
        'status',
        'check_in_date',
        'check_out_date',
        'actual_check_in',
        'actual_check_out',
        'adults',
        'children',
        'total_amount',
        'paid_amount',
        'meta',
    ];

    /**
     * Get cast definitions.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'actual_check_in' => 'datetime',
            'actual_check_out' => 'datetime',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    /**
     * Get the room.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the guest profile.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(GuestProfile::class, 'guest_profile_id');
    }

    /**
     * Get creator user.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope by status.
     */
    public function scopeStatus(Builder $query, ReservationStatus|string $status): Builder
    {
        $value = $status instanceof ReservationStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    /**
     * Scope active reservations.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ReservationStatus::Confirmed->value,
            ReservationStatus::CheckedIn->value,
        ]);
    }
}
