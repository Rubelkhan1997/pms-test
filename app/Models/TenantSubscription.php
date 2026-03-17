<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantUsageRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tenant Subscription Model
 */
class TenantSubscription extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'tenant_id',
        'subscription_plan_id',
        'status',
        'start_date',
        'end_date',
        'trial_ends_at',
        'billing_cycle',
        'amount',
        'last_payment_date',
        'next_billing_date',
        'payment_method',
        'stripe_id',
        'stripe_status',
        'cancelled_at',
        'cancellation_reason',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'trial_ends_at' => 'datetime',
        'last_payment_date' => 'date',
        'next_billing_date' => 'date',
        'amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];
    
    /**
     * Get the tenant that owns the subscription.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    
    /**
     * Get the subscription plan.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }
    
    /**
     * Get usage records.
     */
    public function usageRecords(): HasMany
    {
        return $this->hasMany(TenantUsageRecord::class);
    }
    
    /**
     * Get invoices.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(TenantInvoice::class);
    }
    
    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    
    /**
     * Check if subscription is on trial.
     */
    public function isOnTrial(): bool
    {
        return $this->status === 'trial';
    }
    
    /**
     * Check if subscription is expired.
     */
    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }
    
    /**
     * Check if subscription will auto-renew.
     */
    public function willAutoRenew(): bool
    {
        return $this->isActive() && !$this->end_date;
    }
    
    /**
     * Get days until expiration.
     */
    public function daysUntilExpiration(): ?int
    {
        if (!$this->end_date) {
            return null;
        }
        
        return now()->diffInDays($this->end_date, false);
    }
}
