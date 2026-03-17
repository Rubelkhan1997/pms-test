<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait Auditable
 * 
 * Automatically logs create, update, and delete operations to the audit_logs table.
 * Includes user attribution and IP tracking.
 */
trait Auditable
{
    /**
     * Boot the trait
     */
    protected static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            static::logAudit($model, 'created');
        });
        
        static::updated(function (Model $model) {
            if ($model->wasChanged()) {
                static::logAudit($model, 'updated');
            }
        });
        
        static::deleted(function (Model $model) {
            static::logAudit($model, 'deleted');
        });
    }
    
    /**
     * Log an audit entry
     */
    protected static function logAudit(Model $model, string $action): void
    {
        // Skip if audit logging is disabled
        if (defined('static::DISABLE_AUDITING') && static::DISABLE_AUDITING) {
            return;
        }
        
        // Determine which changes to log
        $changes = match ($action) {
            'created' => $model->getChanges(),
            'updated' => $model->getChanges(),
            'deleted' => $model->getOriginal(),
            default => [],
        };
        
        // Skip if no changes
        if (empty($changes)) {
            return;
        }
        
        // Create audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'old_values' => $action === 'created' ? null : $model->getOriginal(),
            'new_values' => $action === 'deleted' ? null : $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
    
    /**
     * Get audit logs for this model
     */
    public function auditLogs(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}
