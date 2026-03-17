<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class AuditLog
 * 
 * Stores audit trail for all model changes.
 * 
 * @property int $id
 * @property int|null $user_id
 * @property string $action
 * @property string $model_type
 * @property int|string $model_id
 * @property array|null $old_values
 * @property array|null $new_values
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\Carbon $created_at
 */
class AuditLog extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];
    
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];
    
    /**
     * Get the parent auditable model
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
    
    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Scope to filter by action
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }
    
    /**
     * Scope to filter by user
     */
    public function scopeUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
    
    /**
     * Scope to filter by model type
     */
    public function scopeForModel($query, string $modelType)
    {
        return $query->where('model_type', $modelType);
    }
    
    /**
     * Get human-readable action description
     */
    public function getActionDescriptionAttribute(): string
    {
        $modelName = class_basename($this->model_type);
        
        return match ($this->action) {
            'created' => "Created {$modelName}",
            'updated' => "Updated {$modelName}",
            'deleted' => "Deleted {$modelName}",
            default => ucfirst($this->action) . " {$modelName}",
        };
    }
}
