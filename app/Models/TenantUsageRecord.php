<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Tenant;
use App\Models\TenantSubscription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantUsageRecord extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'metric',
        'value',
        'recorded_date',
    ];
    
    protected $casts = [
        'recorded_date' => 'date',
    ];
    
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class);
    }
}
