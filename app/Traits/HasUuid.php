<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Trait HasUuid
 * 
 * Provides UUID primary key support for models.
 * Use this when you need non-sequential, globally unique identifiers.
 */
trait HasUuid
{
    /**
     * Boot the trait
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
    
    /**
     * Get the auto-incrementing key value
     */
    public function getIncrementing(): bool
    {
        return false;
    }
    
    /**
     * Get the auto-incrementing key type
     */
    public function getKeyType(): string
    {
        return 'string';
    }
    
    /**
     * Scope to find by UUID
     */
    public function scopeWhereUuid(Builder $query, string $uuid): Builder
    {
        return $query->where($this->getKeyName(), $uuid);
    }
}
