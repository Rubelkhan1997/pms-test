<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Revenue Category Model
 */
class RevenueCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'gl_account',
        'type',
        'is_taxable',
        'default_tax_rate',
        'is_active',
        'parent_id',
        'sort_order',
    ];

    protected $casts = [
        'is_taxable' => 'boolean',
        'default_tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(RevenueCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(RevenueCategory::class, 'parent_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function folioTransactions(): HasMany
    {
        return $this->hasMany(FolioTransaction::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public static function getForType(string $type = 'revenue'): array
    {
        return static::where('type', $type)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}
