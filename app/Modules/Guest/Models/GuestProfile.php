<?php

declare(strict_types=1);

namespace App\Modules\Guest\Models;

use App\Models\User;
use App\Modules\FrontDesk\Models\Hotel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuestProfile extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'guest_profiles';

    protected $fillable = [
        'hotel_id',
        'agent_id',
        'created_by',
        'reference',
        'status',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'scheduled_at',
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
            'date_of_birth' => 'date',
            'scheduled_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    /**
     * Get hotel.
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get assigned agent.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get creator.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get reservations for this guest.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(\App\Modules\FrontDesk\Models\Reservation::class, 'guest_profile_id');
    }

    /**
     * Scope active guests.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
