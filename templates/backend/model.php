<?php

namespace App\Modules\[MODULE]\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// FILE: app/Modules/[MODULE]/Models/[MODEL].php

class [MODEL] extends Model
{
    use HasFactory, SoftDeletes; // Remove SoftDeletes if not needed

    protected $table = '[TABLE]';

    protected $fillable = [
        // [FILLABLE]
        // Example:
        // 'name',
        // 'code',
        // 'email',
        // 'phone',
        // 'address',
        // 'status',
        // 'hotel_id',
        // [END_FILLABLE]
    ];

    protected $casts = [
        // [CASTS]
        // Example:
        // 'status' => [ENUM_CLASS]::class,
        // 'base_rate' => 'decimal:2',
        // 'is_active' => 'boolean',
        // [END_CASTS]
    ];

    protected $hidden = [
        // 'deleted_at',
    ];

    // [RELATIONSHIPS]
    // Example:
    // public function hotel(): BelongsTo
    // {
    //     return $this->belongsTo(Hotel::class);
    // }
    // 
    // public function rooms(): HasMany
    // {
    //     return $this->hasMany(Room::class);
    // }
    // [END_RELATIONSHIPS]

    /**
     * Scope: Search by keyword
     */
    public function scopeSearch($query, ?string $search = null)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            // [SEARCH_COLUMNS]
            // Example:
            // $q->where('name', 'like', "%{$search}%")
            //   ->orWhere('code', 'like', "%{$search}%")
            //   ->orWhere('email', 'like', "%{$search}%");
            // [END_SEARCH_COLUMNS]
        });
    }

    /**
     * Scope: Filter by status
     */
    public function scopeStatus($query, ?string $status = null)
    {
        if (!$status) {
            return $query;
        }

        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeDateRange($query, ?string $from = null, ?string $to = null)
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }
}
