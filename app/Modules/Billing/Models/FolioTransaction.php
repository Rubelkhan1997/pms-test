<?php

declare(strict_types=1);

namespace App\Modules\Billing\Models;

use App\Models\User;
use App\Modules\FrontDesk\Models\ReservationRoom;
use App\Modules\POS\Models\PosOrder;
use App\Modules\RateAvailability\Models\ChargeCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FolioTransaction extends Model
{
    use HasFactory;

    protected $table = 'folio_transactions';

    protected $fillable = [
        'folio_id', 'charge_code_id', 'reservation_room_id', 'pos_order_id', 'created_by',
        'type', 'description', 'date',
        'quantity', 'unit_price', 'amount', 'tax_rate', 'tax_amount',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'date'       => 'date',
            'quantity'   => 'decimal:2',
            'unit_price' => 'decimal:2',
            'amount'     => 'decimal:2',
            'tax_rate'   => 'decimal:2',
            'tax_amount' => 'decimal:2',
        ];
    }

    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class);
    }

    public function chargeCode(): BelongsTo
    {
        return $this->belongsTo(ChargeCode::class);
    }

    public function reservationRoom(): BelongsTo
    {
        return $this->belongsTo(ReservationRoom::class);
    }

    public function posOrder(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
