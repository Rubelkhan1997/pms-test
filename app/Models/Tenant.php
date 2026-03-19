<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\TenantSubscription;
use App\Models\TenantOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Tenant Model
 * 
 * Represents a hotel/property tenant in the system.
 * Each tenant has their own isolated database.
 * 
 * @property string $id
 * @property string $name
 * @property string|null $company_name
 * @property string $email
 * @property string|null $phone
 * @property string|null $domain
 * @property string|null $subdomain
 * @property string $database_name
 * @property string $status
 * @property string|null $country
 * @property string $timezone
 * @property string $currency
 * @property array|null $settings
 * @property array|null $metadata
 * @property \Carbon\Carbon|null $trial_ends_at
 * @property \Carbon\Carbon|null $activated_at
 * @property \Carbon\Carbon|null $suspended_at
 * @property \Carbon\Carbon|null $cancelled_at
 */
class Tenant extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'domain',
        'subdomain',
        'database_name',
        'status',
        'country',
        'timezone',
        'currency',
        'settings',
        'metadata',
        'trial_ends_at',
        'activated_at',
        'suspended_at',
        'cancelled_at',
    ];
    
    protected $casts = [
        'settings' => 'array',
        'metadata' => 'array',
        'trial_ends_at' => 'datetime',
        'activated_at' => 'datetime',
        'suspended_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
    
    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function ($tenant) {
            if (!$tenant->id) {
                $tenant->id = (string) Str::uuid();
            }
            
            if (!$tenant->database_name) {
                $tenant->database_name = 'tenant_' . str_replace('-', '_', $tenant->id);
            }
        });
    }
    
    /**
     * Get the owners of this tenant.
     */
    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_owners')
            ->withPivot('role')
            ->withTimestamps();
    }
    
    /**
     * Get the active subscription.
     */
    public function subscription(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    /**
     * Get all subscriptions for this tenant.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }
    
    /**
     * Get the active subscription.
     */
    public function activeSubscription(): ?TenantSubscription
    {
        return $this->subscription()
            ->where('status', 'active')
            ->latest()
            ->first();
    }
    
    /**
     * Check if tenant is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    
    /**
     * Check if tenant is on trial.
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }
    
    /**
     * Check if tenant is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }
    
    /**
     * Get database connection name for this tenant.
     */
    public function getDatabaseConnectionName(): string
    {
        return 'tenant_' . $this->id;
    }
    
    /**
     * Activate the tenant.
     */
    public function activate(): void
    {
        $this->update([
            'status' => 'active',
            'activated_at' => now(),
        ]);
    }
    
    /**
     * Suspend the tenant.
     */
    public function suspend(): void
    {
        $this->update([
            'status' => 'suspended',
            'suspended_at' => now(),
        ]);
    }
    
    /**
     * Reactivate the tenant.
     */
    public function reactivate(): void
    {
        $this->update([
            'status' => 'active',
            'suspended_at' => null,
        ]);
    }
    
    /**
     * Cancel the tenant.
     */
    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'metadata' => array_merge(
                $this->metadata ?? [],
                ['cancellation_reason' => $reason]
            ),
        ]);
    }
}
