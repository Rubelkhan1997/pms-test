<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use App\Models\User;
use App\Modules\Billing\Models\Folio;
use App\Modules\Channel\Models\Channel;
use App\Modules\Guest\Models\Agent;
use App\Modules\Guest\Models\Company;
use App\Modules\Guest\Models\Guest;
use App\Modules\RateAvailability\Models\CancellationPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'reservations';

    protected $fillable = [
        'property_id', 'guest_id', 'agent_id', 'company_id', 'channel_id',
        'cancellation_policy_id', 'created_by', 'reference', 'source',
        'channel_reference', 'group_name', 'status',
        'check_in_date', 'check_out_date', 'adults', 'children',
        'subtotal', 'discount_amount', 'tax_amount', 'total_amount',
        'paid_amount', 'balance', 'special_requests',
        'arrival_time', 'departure_time',
        'confirmed_at', 'cancelled_at', 'cancellation_reason', 'no_show_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date'   => 'date',
            'check_out_date'  => 'date',
            'subtotal'        => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'total_amount'    => 'decimal:2',
            'paid_amount'     => 'decimal:2',
            'balance'         => 'decimal:2',
            'confirmed_at'    => 'datetime',
            'cancelled_at'    => 'datetime',
            'no_show_at'      => 'datetime',
            'meta'            => 'array',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function cancellationPolicy(): BelongsTo
    {
        return $this->belongsTo(CancellationPolicy::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(ReservationRoom::class);
    }

    public function guests(): BelongsToMany
    {
        return $this->belongsToMany(Guest::class, 'reservation_guests')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function folios(): HasMany
    {
        return $this->hasMany(Folio::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['confirmed', 'checked_in']);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
