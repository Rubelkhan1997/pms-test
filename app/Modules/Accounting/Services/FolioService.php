<?php

declare(strict_types=1);

namespace App\Modules\Accounting\Services;

use App\Models\Hotel;
use App\Modules\Accounting\Models\Folio;
use App\Modules\Accounting\Models\LedgerEntry;
use App\Modules\FrontDesk\Models\Reservation;

/**
 * Folio Service
 * 
 * Manages guest folios:
 * - Create folios
 * - Post charges
 * - Post payments
 * - Folio transfers
 * - Folio settlement
 */
class FolioService
{
    /**
     * Create folio for reservation.
     *
     * @param  Reservation  $reservation  Reservation model
     * @param  string  $type  Folio type (guest, master, non_guest)
     * @return Folio Created folio
     */
    public function createForReservation(Reservation $reservation, string $type = 'guest'): Folio
    {
        $hotel = Hotel::findOrFail($reservation->hotel_id);
        
        $folioNumber = $this->generateFolioNumber($hotel->id);
        
        return Folio::create([
            'hotel_id' => $hotel->id,
            'reservation_id' => $reservation->id,
            'guest_profile_id' => $reservation->guest_profile_id,
            'folio_number' => $folioNumber,
            'type' => $type,
            'status' => 'open',
            'open_date' => $reservation->check_in_date,
            'close_date' => null,
        ]);
    }
    
    /**
     * Post charge to folio.
     *
     * @param  Folio  $folio  Folio model
     * @param  array<string, mixed>  $chargeData  Charge data
     * @return LedgerEntry Created ledger entry
     */
    public function postCharge(Folio $folio, array $chargeData): LedgerEntry
    {
        $ledgerService = app(LedgerService::class);
        
        $entry = $ledgerService->postCharge([
            'hotel_id' => $folio->hotel_id,
            'folio_id' => $folio->id,
            'description' => $chargeData['description'],
            'amount' => $chargeData['amount'],
            'entry_date' => $chargeData['date'] ?? now(),
            'created_by' => $chargeData['created_by'] ?? auth()->id(),
            'metadata' => $chargeData['metadata'] ?? [],
        ]);
        
        // Update folio totals
        $folio->increment('total_charges', $chargeData['amount']);
        $folio->increment('balance', $chargeData['amount']);
        
        return $entry;
    }
    
    /**
     * Post payment to folio.
     *
     * @param  Folio  $folio  Folio model
     * @param  array<string, mixed>  $paymentData  Payment data
     * @return \App\Modules\Accounting\Models\Payment Created payment
     */
    public function postPayment(Folio $folio, array $paymentData)
    {
        $paymentService = app(PaymentService::class);
        
        // Create payment without invoice
        $payment = \App\Modules\Accounting\Models\Payment::create([
            'hotel_id' => $folio->hotel_id,
            'folio_id' => $folio->id,
            'payment_number' => $this->generatePaymentNumber($folio->hotel_id),
            'method' => $paymentData['method'] ?? 'cash',
            'amount' => $paymentData['amount'],
            'payment_date' => $paymentData['payment_date'] ?? now(),
            'transaction_id' => $paymentData['transaction_id'] ?? null,
            'reference_number' => $paymentData['reference_number'] ?? null,
            'notes' => $paymentData['notes'] ?? null,
            'metadata' => $paymentData['metadata'] ?? [],
        ]);
        
        // Update folio
        $folio->increment('total_payments', $payment->amount);
        $folio->decrement('balance', $payment->amount);
        
        // Create ledger entries
        $ledgerService = app(LedgerService::class);
        $ledgerService->postPayment($payment);
        
        // Close folio if balance is zero
        if ($folio->balance <= 0) {
            $this->closeFolio($folio);
        }
        
        return $payment;
    }
    
    /**
     * Transfer charge from one folio to another.
     *
     * @param  Folio  $fromFolio  Source folio
     * @param  Folio  $toFolio  Destination folio
     * @param  float  $amount  Amount to transfer
     * @param  string  $description  Transfer description
     * @return array{debit: LedgerEntry, credit: LedgerEntry}
     */
    public function transferCharge(
        Folio $fromFolio,
        Folio $toFolio,
        float $amount,
        string $description
    ): array {
        $ledgerService = app(LedgerService::class);
        
        // Debit: To folio (receivable)
        $debitEntry = LedgerEntry::create([
            'hotel_id' => $toFolio->hotel_id,
            'folio_id' => $toFolio->id,
            'entry_type' => 'transfer',
            'account_type' => 'receivable',
            'description' => "{$description} - Transfer from {$fromFolio->folio_number}",
            'debit' => $amount,
            'credit' => 0,
            'entry_date' => now(),
            'created_by' => auth()->id() ?? null,
        ]);
        
        // Credit: From folio (receivable)
        $creditEntry = LedgerEntry::create([
            'hotel_id' => $fromFolio->hotel_id,
            'folio_id' => $fromFolio->id,
            'entry_type' => 'transfer',
            'account_type' => 'receivable',
            'description' => "{$description} - Transfer to {$toFolio->folio_number}",
            'debit' => 0,
            'credit' => $amount,
            'entry_date' => now(),
            'created_by' => auth()->id() ?? null,
            'metadata' => ['paired_entry_id' => $debitEntry->id],
        ]);
        
        // Update folios
        $fromFolio->decrement('total_charges', $amount);
        $fromFolio->decrement('balance', $amount);
        
        $toFolio->increment('total_charges', $amount);
        $toFolio->increment('balance', $amount);
        
        return ['debit' => $debitEntry, 'credit' => $creditEntry];
    }
    
    /**
     * Close folio.
     */
    public function closeFolio(Folio $folio): void
    {
        $folio->update([
            'status' => 'closed',
            'close_date' => now(),
        ]);
    }
    
    /**
     * Reopen closed folio.
     */
    public function reopenFolio(Folio $folio): void
    {
        $folio->update([
            'status' => 'open',
            'close_date' => null,
        ]);
    }
    
    /**
     * Get folio by reservation.
     */
    public function getByReservation(int $reservationId, string $status = 'open'): ?Folio
    {
        $query = Folio::where('reservation_id', $reservationId);
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        return $query->latest('id')->first();
    }
    
    /**
     * Get open folios for hotel.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Folio>
     */
    public function getOpenFolios(int $hotelId)
    {
        return Folio::where('hotel_id', $hotelId)
            ->where('status', 'open')
            ->orderBy('folio_number')
            ->get();
    }
    
    /**
     * Generate unique folio number.
     */
    protected function generateFolioNumber(int $hotelId): string
    {
        $prefix = 'F';
        $year = now()->format('Y');
        
        $lastFolio = Folio::where('hotel_id', $hotelId)
            ->where('folio_number', 'like', "{$prefix}-{$year}/%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastFolio) {
            preg_match('/(\d+)$/', $lastFolio->folio_number, $matches);
            $nextNumber = isset($matches[1]) ? (int) $matches[1] + 1 : 1;
        } else {
            $nextNumber = 1;
        }
        
        return "{$prefix}-{$year}/" . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate unique payment number.
     */
    protected function generatePaymentNumber(int $hotelId): string
    {
        $prefix = 'P';
        $year = now()->format('Y');
        
        $lastPayment = \App\Modules\Accounting\Models\Payment::where('hotel_id', $hotelId)
            ->where('folio_id', '!=', null)
            ->where('payment_number', 'like', "{$prefix}-{$year}/%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastPayment) {
            preg_match('/(\d+)$/', $lastPayment->payment_number, $matches);
            $nextNumber = isset($matches[1]) ? (int) $matches[1] + 1 : 1;
        } else {
            $nextNumber = 1;
        }
        
        return "{$prefix}-{$year}/" . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get folio statistics.
     */
    public function getStatistics(int $hotelId): array
    {
        $openFolios = Folio::where('hotel_id', $hotelId)->where('status', 'open');
        
        return [
            'open_folios' => $openFolios->count(),
            'total_charges' => $openFolios->sum('total_charges'),
            'total_payments' => $openFolios->sum('total_payments'),
            'total_balance' => $openFolios->sum('balance'),
            'high_balance_count' => (clone $openFolios)->where('balance', '>', 1000)->count(),
        ];
    }
}
