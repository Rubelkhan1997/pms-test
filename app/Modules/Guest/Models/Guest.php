<?php

declare(strict_types=1);

namespace App\Modules\Guest\Models;

use App\Models\User;
use App\Modules\FrontDesk\Models\Property;
use App\Modules\FrontDesk\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'guests';

    protected $fillable = [
        'property_id', 'agent_id', 'company_id', 'created_by',
        'reference', 'title', 'first_name', 'last_name', 'email', 'phone',
        'gender', 'date_of_birth', 'nationality', 'language',
        'id_type', 'id_number', 'id_expiry',
        'vip_level', 'notes', 'status', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'id_expiry'     => 'date',
            'meta'          => 'array',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(GuestDocument::class);
    }

    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class, 'reservation_guests')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
