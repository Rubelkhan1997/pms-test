<?php

declare(strict_types=1);

namespace App\Modules\Guest\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'agents';

    protected $fillable = [
        'hotel_id',
        'name',
        'email',
        'phone',
        'commission_rate',
    ];

    /**
     * Get guests handled by the agent.
     */
    public function guests(): HasMany
    {
        return $this->hasMany(GuestProfile::class, 'agent_id');
    }
}


