<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Central User Model
 * 
 * Users in the central database can own/manage multiple tenants.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'is_active',
        'phone',
        'company_name',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get tenants owned/managed by this user.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_owners')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get active tenants for this user.
     */
    public function activeTenants()
    {
        return $this->tenants()->where('status', 'active');
    }

    /**
     * Get current tenant (from session).
     */
    public function getCurrentTenantAttribute(): ?Tenant
    {
        $tenantId = session('current_tenant_id');
        
        if ($tenantId) {
            return $this->tenants()->find($tenantId);
        }
        
        // Return first active tenant as default
        return $this->activeTenants()->first();
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if user is tenant owner.
     */
    public function isTenantOwner(Tenant $tenant): bool
    {
        return $this->tenants()
            ->wherePivot('role', 'owner')
            ->where('tenant_id', $tenant->id)
            ->exists();
    }

    /**
     * Check if user has access to tenant.
     */
    public function hasAccessToTenant(Tenant $tenant): bool
    {
        return $this->isSuperAdmin() || $this->tenants()->where('tenant_id', $tenant->id)->exists();
    }

    /**
     * Switch to a different tenant.
     */
    public function switchTenant(?Tenant $tenant): bool
    {
        if (!$tenant) {
            session()->forget('current_tenant_id');
            return true;
        }
        
        if (!$this->hasAccessToTenant($tenant)) {
            return false;
        }
        
        session(['current_tenant_id' => $tenant->id]);
        
        return true;
    }

    /**
     * Record login information.
     */
    public function recordLogin(string $ipAddress): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ]);
    }

    /**
     * Create API token for tenant access.
     */
    public function createTenantToken(Tenant $tenant, string $name, array $abilities = ['*']): \Laravel\Sanctum\NewAccessToken
    {
        return $this->createToken($name, array_merge($abilities, [
            'tenant:' . $tenant->id,
        ]));
    }
}
