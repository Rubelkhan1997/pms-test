<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\TenantSubscription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Subscription Plan Model
 */
class SubscriptionPlan extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'code',
        'description',
        'price_monthly',
        'price_yearly',
        'trial_days',
        'max_properties',
        'max_rooms',
        'max_users',
        'features',
        'limits',
        'is_active',
        'sort_order',
    ];
    
    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'features' => 'array',
        'limits' => 'array',
        'is_active' => 'boolean',
    ];
    
    /**
     * Get subscriptions using this plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class, 'subscription_plan_id');
    }
    
    /**
     * Get active subscriptions count.
     */
    public function getActiveSubscriptionsCountAttribute(): int
    {
        return $this->subscriptions()->where('status', 'active')->count();
    }
    
    /**
     * Check if plan has feature.
     */
    public function hasFeature(string $feature): bool
    {
        $features = $this->features ?? [];
        return in_array($feature, $features, true);
    }
    
    /**
     * Get limit for metric.
     */
    public function getLimit(string $metric): ?int
    {
        $limits = $this->limits ?? [];
        return $limits[$metric] ?? null;
    }
}
