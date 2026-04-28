<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'landlord';

    protected $fillable = [
        'name', 'slug', 'domain', 'database',
        'status', 'trial_ends_at', 'plan_id',
        'contact_name', 'contact_email', 'contact_phone',
        'email_verified_at',
    ];

    protected $casts = [
        'trial_ends_at'   => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial'], true);
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isFuture();
    }

    public function isTrialExpired(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isPast();
    }

    public function getDatabaseName(): string
    {
        return $this->database;
    }
}
