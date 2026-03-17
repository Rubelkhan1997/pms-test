<?php

declare(strict_types=1);

namespace App\Modules\Accounting\Services;

use App\Models\Hotel;
use App\Modules\Accounting\Models\Payment;
use App\Modules\Accounting\Models\Invoice;
use App\Modules\Accounting\Models\Folio;
use App\Modules\Accounting\Models\LedgerEntry;
use Carbon\Carbon;

/**
 * Payment Service
 * 
 * Manages payment processing:
 * - Record payments
 * - Allocate to invoices
 * - Payment methods
 * - Refunds
 * - Payment reconciliation
 */
class PaymentService
{
    /**
     * Process payment for invoice.
     *
     * @param  Invoice  $invoice  Invoice model
     * @param  array<string, mixed>  $paymentData  Payment data
     * @return Payment Processed payment
     */
    public function processForInvoice(Invoice $invoice, array $paymentData): Payment
    {
        $hotel = Hotel::findOrFail($invoice->hotel_id);
        
        $amount = (float) ($paymentData['amount'] ?? $invoice->balance);
        
        // Create payment record
        $payment = Payment::create([
            'hotel_id' => $hotel->id,
            'invoice_id' => $invoice->id,
            'payment_number' => $this->generatePaymentNumber($hotel->id),
            'method' => $paymentData['method'] ?? 'cash',
            'amount' => $amount,
            'payment_date' => $paymentData['payment_date'] ?? now(),
            'transaction_id' => $paymentData['transaction_id'] ?? null,
            'reference_number' => $paymentData['reference_number'] ?? null,
            'notes' => $paymentData['notes'] ?? null,
            'metadata' => $paymentData['metadata'] ?? [],
        ]);
        
        // Update invoice
        $this->applyPaymentToInvoice($payment, $invoice);
        
        // Create ledger entries
        $this->createPaymentLedgerEntries($payment);
        
        return $payment;
    }
    
    /**
     * Process payment for folio.
     *
     * @param  Folio  $folio  Folio model
     * @param  array<string, mixed>  $paymentData  Payment data
     * @return Payment Processed payment
     */
    public function processForFolio(Folio $folio, array $paymentData): Payment
    {
        $hotel = Hotel::findOrFail($folio->hotel_id);
        
        $amount = (float) ($paymentData['amount'] ?? $folio->balance);
        
        $payment = Payment::create([
            'hotel_id' => $hotel->id,
            'folio_id' => $folio->id,
            'payment_number' => $this->generatePaymentNumber($hotel->id),
            'method' => $paymentData['method'] ?? 'cash',
            'amount' => $amount,
            'payment_date' => $paymentData['payment_date'] ?? now(),
            'transaction_id' => $paymentData['transaction_id'] ?? null,
            'reference_number' => $paymentData['reference_number'] ?? null,
            'notes' => $paymentData['notes'] ?? null,
            'metadata' => $paymentData['metadata'] ?? [],
        ]);
        
        // Update folio
        $folio->increment('total_payments', $amount);
        $folio->decrement('balance', $amount);
        
        // Create ledger entries
        $this->createPaymentLedgerEntries($payment);
        
        return $payment;
    }
    
    /**
     * Apply payment to invoice.
     */
    protected function applyPaymentToInvoice(Payment $payment, Invoice $invoice): void
    {
        $invoice->increment('paid_amount', $payment->amount);
        $invoice->decrement('balance', $payment->amount);
        
        // Mark as paid if balance is zero
        if ($invoice->balance <= 0) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }
    }
    
    /**
     * Create ledger entries for payment.
     */
    protected function createPaymentLedgerEntries(Payment $payment): void
    {
        // Debit: Cash/Bank account
        LedgerEntry::create([
            'hotel_id' => $payment->hotel_id,
            'payment_id' => $payment->id,
            'entry_type' => 'payment',
            'account_type' => 'cash',
            'description' => "Payment received - {$payment->payment_number}",
            'debit' => $payment->amount,
            'credit' => 0,
            'entry_date' => $payment->payment_date,
            'created_by' => auth()->id() ?? null,
        ]);
        
        // Credit: Accounts Receivable
        LedgerEntry::create([
            'hotel_id' => $payment->hotel_id,
            'payment_id' => $payment->id,
            'entry_type' => 'payment',
            'account_type' => 'receivable',
            'description' => "Payment received - {$payment->payment_number}",
            'debit' => 0,
            'credit' => $payment->amount,
            'entry_date' => $payment->payment_date,
            'created_by' => auth()->id() ?? null,
        ]);
    }
    
    /**
     * Process refund.
     *
     * @param  Payment  $originalPayment  Original payment
     * @param  array<string, mixed>  $refundData  Refund data
     * @return Payment Refund payment record
     */
    public function processRefund(Payment $originalPayment, array $refundData): Payment
    {
        $hotel = Hotel::findOrFail($originalPayment->hotel_id);
        
        $refundAmount = (float) ($refundData['amount'] ?? $originalPayment->amount);
        
        $refund = Payment::create([
            'hotel_id' => $hotel->id,
            'invoice_id' => $originalPayment->invoice_id,
            'payment_number' => $this->generatePaymentNumber($hotel->id, 'REF'),
            'method' => 'refund',
            'amount' => -$refundAmount, // Negative for refund
            'payment_date' => $refundData['payment_date'] ?? now(),
            'transaction_id' => $refundData['transaction_id'] ?? null,
            'reference_number' => $refundData['reference_number'] ?? null,
            'notes' => $refundData['reason'] ?? 'Refund',
            'metadata' => array_merge(
                ['original_payment_id' => $originalPayment->id],
                $refundData['metadata'] ?? []
            ),
        ]);
        
        // Reverse ledger entries
        $this->createRefundLedgerEntries($refund);
        
        return $refund;
    }
    
    /**
     * Create ledger entries for refund.
     */
    protected function createRefundLedgerEntries(Payment $refund): void
    {
        // Reverse of payment entries
        LedgerEntry::create([
            'hotel_id' => $refund->hotel_id,
            'payment_id' => $refund->id,
            'entry_type' => 'refund',
            'account_type' => 'cash',
            'description' => "Refund issued - {$refund->payment_number}",
            'debit' => 0,
            'credit' => abs($refund->amount),
            'entry_date' => $refund->payment_date,
            'created_by' => auth()->id() ?? null,
        ]);
        
        LedgerEntry::create([
            'hotel_id' => $refund->hotel_id,
            'payment_id' => $refund->id,
            'entry_type' => 'refund',
            'account_type' => 'receivable',
            'description' => "Refund issued - {$refund->payment_number}",
            'debit' => abs($refund->amount),
            'credit' => 0,
            'entry_date' => $refund->payment_date,
            'created_by' => auth()->id() ?? null,
        ]);
    }
    
    /**
     * Generate unique payment number.
     */
    protected function generatePaymentNumber(int $hotelId, string $prefix = 'PAY'): string
    {
        $year = now()->format('Y');
        
        $lastPayment = Payment::where('hotel_id', $hotelId)
            ->where('payment_number', 'like', "{$prefix}/{$year}/%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastPayment) {
            preg_match('/(\d+)$/', $lastPayment->payment_number, $matches);
            $nextNumber = isset($matches[1]) ? (int) $matches[1] + 1 : 1;
        } else {
            $nextNumber = 1;
        }
        
        return "{$prefix}/{$year}/" . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get payments by method.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Payment>
     */
    public function getByMethod(int $hotelId, string $method, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $query = Payment::where('hotel_id', $hotelId)->where('method', $method);
        
        if ($startDate) {
            $query->where('payment_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('payment_date', '<=', $endDate);
        }
        
        return $query->orderBy('payment_date', 'desc')->get();
    }
    
    /**
     * Get payment statistics.
     */
    public function getStatistics(int $hotelId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = Payment::where('hotel_id', $hotelId)->where('amount', '>', 0);
        
        if ($startDate) {
            $query->where('payment_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('payment_date', '<=', $endDate);
        }
        
        $totalPayments = $query->count();
        $totalAmount = $query->sum('amount');
        
        $byMethod = [];
        foreach (['cash', 'credit_card', 'debit_card', 'bank_transfer', 'check'] as $method) {
            $byMethod[$method] = (clone $query)->where('method', $method)->sum('amount');
        }
        
        return [
            'total_payments' => $totalPayments,
            'total_amount' => $totalAmount,
            'by_method' => $byMethod,
        ];
    }
    
    /**
     * Reconcile payment with bank statement.
     */
    public function reconcilePayment(Payment $payment, string $bankReference): void
    {
        $payment->update([
            'metadata' => array_merge(
                $payment->metadata ?? [],
                ['bank_reference' => $bankReference, 'reconciled_at' => now()]
            ),
        ]);
    }
}
