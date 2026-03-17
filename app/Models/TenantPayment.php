<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Tenant;
use App\Models\TenantInvoice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPayment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'payment_number',
        'method',
        'amount',
        'payment_date',
        'transaction_id',
        'reference_number',
        'notes',
        'metadata',
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'metadata' => 'array',
    ];
    
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(TenantInvoice::class);
    }
}
