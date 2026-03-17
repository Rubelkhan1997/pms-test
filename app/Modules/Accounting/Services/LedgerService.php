<?php

declare(strict_types=1);

namespace App\Modules\Accounting\Services;

use App\Models\Hotel;
use App\Modules\Accounting\Models\LedgerEntry;
use App\Modules\Accounting\Models\Invoice;
use App\Modules\Accounting\Models\Payment;
use Carbon\Carbon;

/**
 * Ledger Service
 * 
 * Manages double-entry bookkeeping:
 * - Post journal entries
 * - Account balances
 * - Trial balance
 * - Financial reports
 */
class LedgerService
{
    /**
     * Post charge to ledger.
     *
     * @param  array<string, mixed>  $entryData  Entry data
     * @return LedgerEntry Created ledger entry
     */
    public function postCharge(array $entryData): LedgerEntry
    {
        $amount = (float) $entryData['amount'];
        
        // Debit: Accounts Receivable
        $debitEntry = LedgerEntry::create([
            'hotel_id' => $entryData['hotel_id'],
            'folio_id' => $entryData['folio_id'] ?? null,
            'invoice_id' => $entryData['invoice_id'] ?? null,
            'entry_type' => 'charge',
            'account_type' => 'receivable',
            'description' => $entryData['description'],
            'debit' => $amount,
            'credit' => 0,
            'entry_date' => $entryData['entry_date'] ?? now(),
            'created_by' => $entryData['created_by'] ?? auth()->id(),
            'metadata' => $entryData['metadata'] ?? [],
        ]);
        
        // Credit: Revenue account
        $creditEntry = LedgerEntry::create([
            'hotel_id' => $entryData['hotel_id'],
            'folio_id' => $entryData['folio_id'] ?? null,
            'invoice_id' => $entryData['invoice_id'] ?? null,
            'entry_type' => 'charge',
            'account_type' => 'revenue',
            'description' => $entryData['description'],
            'debit' => 0,
            'credit' => $amount,
            'entry_date' => $entryData['entry_date'] ?? now(),
            'created_by' => $entryData['created_by'] ?? auth()->id(),
            'metadata' => array_merge(
                $entryData['metadata'] ?? [],
                ['paired_entry_id' => $debitEntry->id]
            ),
        ]);
        
        return $debitEntry;
    }
    
    /**
     * Post payment to ledger.
     *
     * @param  Payment  $payment  Payment model
     * @return LedgerEntry Created ledger entry
     */
    public function postPayment(Payment $payment): LedgerEntry
    {
        $amount = abs($payment->amount);
        
        // Debit: Cash/Bank
        $debitEntry = LedgerEntry::create([
            'hotel_id' => $payment->hotel_id,
            'payment_id' => $payment->id,
            'entry_type' => 'payment',
            'account_type' => 'cash',
            'description' => "Payment received - {$payment->payment_number}",
            'debit' => $amount,
            'credit' => 0,
            'entry_date' => $payment->payment_date,
            'created_by' => auth()->id() ?? null,
        ]);
        
        // Credit: Accounts Receivable
        $creditEntry = LedgerEntry::create([
            'hotel_id' => $payment->hotel_id,
            'payment_id' => $payment->id,
            'entry_type' => 'payment',
            'account_type' => 'receivable',
            'description' => "Payment received - {$payment->payment_number}",
            'debit' => 0,
            'credit' => $amount,
            'entry_date' => $payment->payment_date,
            'created_by' => auth()->id() ?? null,
            'metadata' => ['paired_entry_id' => $debitEntry->id],
        ]);
        
        return $debitEntry;
    }
    
    /**
     * Post adjustment to ledger.
     *
     * @param  array<string, mixed>  $entryData  Entry data
     * @return array{debit: LedgerEntry, credit: LedgerEntry}
     */
    public function postAdjustment(array $entryData): array
    {
        $amount = (float) $entryData['amount'];
        
        $debitEntry = LedgerEntry::create([
            'hotel_id' => $entryData['hotel_id'],
            'entry_type' => 'adjustment',
            'account_type' => $entryData['debit_account'] ?? 'adjustments',
            'description' => $entryData['description'],
            'debit' => $amount,
            'credit' => 0,
            'entry_date' => $entryData['entry_date'] ?? now(),
            'created_by' => $entryData['created_by'] ?? auth()->id(),
            'metadata' => $entryData['metadata'] ?? [],
        ]);
        
        $creditEntry = LedgerEntry::create([
            'hotel_id' => $entryData['hotel_id'],
            'entry_type' => 'adjustment',
            'account_type' => $entryData['credit_account'] ?? 'receivable',
            'description' => $entryData['description'],
            'debit' => 0,
            'credit' => $amount,
            'entry_date' => $entryData['entry_date'] ?? now(),
            'created_by' => $entryData['created_by'] ?? auth()->id(),
            'metadata' => ['paired_entry_id' => $debitEntry->id],
        ]);
        
        return ['debit' => $debitEntry, 'credit' => $creditEntry];
    }
    
    /**
     * Get account balance.
     */
    public function getAccountBalance(int $hotelId, string $accountType, ?Carbon $endDate = null): float
    {
        $query = LedgerEntry::where('hotel_id', $hotelId)
            ->where('account_type', $accountType);
        
        if ($endDate) {
            $query->where('entry_date', '<=', $endDate);
        }
        
        $totalDebit = (clone $query)->sum('debit');
        $totalCredit = (clone $query)->sum('credit');
        
        return match ($accountType) {
            'asset', 'receivable', 'cash' => $totalDebit - $totalCredit,
            'liability', 'equity', 'revenue' => $totalCredit - $totalDebit,
            default => $totalDebit - $totalCredit,
        };
    }
    
    /**
     * Get trial balance.
     *
     * @return array<string, float>
     */
    public function getTrialBalance(int $hotelId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = LedgerEntry::where('hotel_id', $hotelId);
        
        if ($startDate) {
            $query->where('entry_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('entry_date', '<=', $endDate);
        }
        
        $accounts = [
            'cash' => ['debit' => 0, 'credit' => 0],
            'receivable' => ['debit' => 0, 'credit' => 0],
            'revenue' => ['debit' => 0, 'credit' => 0],
            'adjustments' => ['debit' => 0, 'credit' => 0],
        ];
        
        foreach ($accounts as $accountType => $balance) {
            $entries = (clone $query)->where('account_type', $accountType)->get();
            $accounts[$accountType] = [
                'debit' => $entries->sum('debit'),
                'credit' => $entries->sum('credit'),
                'balance' => $this->getAccountBalance($hotelId, $accountType, $endDate),
            ];
        }
        
        return $accounts;
    }
    
    /**
     * Get ledger for folio.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, LedgerEntry>
     */
    public function getFolioLedger(int $folioId)
    {
        return LedgerEntry::where('folio_id', $folioId)
            ->orderBy('entry_date')
            ->orderBy('created_at')
            ->get();
    }
    
    /**
     * Get ledger for invoice.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, LedgerEntry>
     */
    public function getInvoiceLedger(int $invoiceId)
    {
        return LedgerEntry::where('invoice_id', $invoiceId)
            ->orderBy('entry_date')
            ->orderBy('created_at')
            ->get();
    }
    
    /**
     * Get daily transaction report.
     *
     * @return array{charges: float, payments: float, adjustments: float}
     */
    public function getDailyReport(int $hotelId, Carbon $date): array
    {
        $charges = LedgerEntry::where('hotel_id', $hotelId)
            ->where('entry_type', 'charge')
            ->whereDate('entry_date', $date)
            ->sum('debit');
        
        $payments = LedgerEntry::where('hotel_id', $hotelId)
            ->where('entry_type', 'payment')
            ->whereDate('entry_date', $date)
            ->sum('debit');
        
        $adjustments = LedgerEntry::where('hotel_id', $hotelId)
            ->where('entry_type', 'adjustment')
            ->whereDate('entry_date', $date)
            ->sum('debit');
        
        return [
            'charges' => $charges,
            'payments' => $payments,
            'adjustments' => $adjustments,
            'date' => $date->toDateString(),
        ];
    }
    
    /**
     * Verify ledger balances (debits = credits).
     */
    public function verifyBalance(int $hotelId, ?Carbon $endDate = null): bool
    {
        $query = LedgerEntry::where('hotel_id', $hotelId);
        
        if ($endDate) {
            $query->where('entry_date', '<=', $endDate);
        }
        
        $totalDebit = $query->sum('debit');
        $totalCredit = $query->sum('credit');
        
        return abs($totalDebit - $totalCredit) < 0.01; // Allow for rounding
    }
    
    /**
     * Get unbalanced entries.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, LedgerEntry>
     */
    public function getUnbalancedEntries(int $hotelId)
    {
        // This would need more complex logic to find orphaned entries
        // For now, return entries without paired_entry_id in metadata
        return LedgerEntry::where('hotel_id', $hotelId)
            ->whereJsonDoesntContain('metadata->paired_entry_id', '!=', null)
            ->get();
    }
}
