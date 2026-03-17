<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Hotel
 * 
 * Represents a hotel property in the multi-tenant system.
 * 
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $timezone
 * @property string|null $currency
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $address
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Hotel extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'name',
        'code',
        'timezone',
        'currency',
        'email',
        'phone',
        'address',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();
        
        // Auto-generate code from name if not provided
        static::creating(function ($hotel) {
            if (!$hotel->code && $hotel->name) {
                $hotel->code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $hotel->name), 0, 3));
            }
        });
    }
    
    /**
     * Get all rooms for the hotel
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(\App\Modules\FrontDesk\Models\Room::class);
    }
    
    /**
     * Get all reservations for the hotel
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(\App\Modules\FrontDesk\Models\Reservation::class);
    }
    
    /**
     * Get all guest profiles for the hotel
     */
    public function guestProfiles(): HasMany
    {
        return $this->hasMany(\App\Modules\Guest\Models\GuestProfile::class);
    }
    
    /**
     * Get all agents for the hotel
     */
    public function agents(): HasMany
    {
        return $this->hasMany(\App\Modules\Guest\Models\Agent::class);
    }
    
    /**
     * Get all employees for the hotel
     */
    public function employees(): HasMany
    {
        return $this->hasMany(\App\Modules\Hr\Models\Employee::class);
    }
    
    /**
     * Scope to only active hotels
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Get full name with code
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->name} ({$this->code})";
    }
}
