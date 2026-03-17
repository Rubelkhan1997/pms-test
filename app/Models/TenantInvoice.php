<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\TenantInvoiceItem;
use App\Models\TenantPayment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Tenant Invoice Model
 */
class TenantInvoice extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'invoice_number',
        'status',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance',
        'stripe_invoice_id',
        'sent_at',
        'paid_at',
        'notes',
    ];
    
    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
    ];
    
    /**
     * Get the tenant that owns the invoice.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    
    /**
     * Get the subscription.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class);
    }
    
    /**
     * Get invoice items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TenantInvoiceItem::class);
    }
    
    /**
     * Get payments.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(TenantPayment::class);
    }
    
    /**
     * Check if invoice is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
    
    /**
     * Check if invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date && $this->due_date->isPast();
    }
    
    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'balance' => 0,
        ]);
    }
}
