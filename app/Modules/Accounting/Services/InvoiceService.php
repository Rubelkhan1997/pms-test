<?php

declare(strict_types=1);

namespace App\Modules\Accounting\Services;

use App\Models\Hotel;
use App\Modules\Accounting\Models\Invoice;
use App\Modules\Accounting\Models\InvoiceItem;
use App\Modules\Accounting\Models\Folio;
use App\Modules\FrontDesk\Models\Reservation;
use Carbon\Carbon;

/**
 * Invoice Service
 * 
 * Manages invoice generation and lifecycle:
 * - Guest invoices
 * - Corporate invoices
 * - Folio-based invoicing
 * - Tax calculation
 * - Invoice numbering
 */
class InvoiceService
{
    /**
     * Generate invoice from reservation.
     *
     * @param  Reservation  $reservation  Reservation model
     * @param  array<string, mixed>  $options  Invoice options
     * @return Invoice Generated invoice
     */
    public function generateFromReservation(
        Reservation $reservation,
        array $options = []
    ): Invoice {
        $hotel = Hotel::findOrFail($reservation->hotel_id);
        
        // Calculate invoice totals
        $items = $this->buildInvoiceItems($reservation, $options);
        $subtotal = collect($items)->sum('total');
        $taxAmount = $this->calculateTax($subtotal, $hotel->id);
        $totalAmount = $subtotal + $taxAmount;
        
        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber($hotel->id);
        
        // Create invoice
        $invoice = Invoice::create([
            'hotel_id' => $hotel->id,
            'reservation_id' => $reservation->id,
            'guest_profile_id' => $reservation->guest_profile_id,
            'invoice_number' => $invoiceNumber,
            'status' => 'draft',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'balance' => $totalAmount,
            'notes' => $options['notes'] ?? null,
            'terms' => $options['terms'] ?? null,
        ]);
        
        // Create invoice items
        foreach ($items as $itemData) {
            $invoice->items()->create($itemData);
        }
        
        return $invoice;
    }
    
    /**
     * Generate invoice from folio.
     *
     * @param  Folio  $folio  Folio model
     * @return Invoice Generated invoice
     */
    public function generateFromFolio(Folio $folio): Invoice
    {
        $hotel = Hotel::findOrFail($folio->hotel_id);
        
        // Get folio charges
        $charges = $folio->charges()->where('type', 'charge')->get();
        
        $items = [];
        foreach ($charges as $charge) {
            $items[] = [
                'description' => $charge->description,
                'date' => $charge->date,
                'quantity' => 1,
                'unit_price' => $charge->amount,
                'total' => $charge->amount,
                'type' => 'charge',
            ];
        }
        
        $subtotal = collect($items)->sum('total');
        $taxAmount = $this->calculateTax($subtotal, $hotel->id);
        $totalAmount = $subtotal + $taxAmount;
        
        $invoiceNumber = $this->generateInvoiceNumber($hotel->id);
        
        $invoice = Invoice::create([
            'hotel_id' => $hotel->id,
            'folio_id' => $folio->id,
            'guest_profile_id' => $folio->guest_profile_id,
            'invoice_number' => $invoiceNumber,
            'status' => 'draft',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => $folio->total_payments,
            'balance' => $totalAmount - $folio->total_payments,
        ]);
        
        foreach ($items as $itemData) {
            $invoice->items()->create($itemData);
        }
        
        return $invoice;
    }
    
    /**
     * Build invoice items from reservation.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildInvoiceItems(
        Reservation $reservation,
        array $options
    ): array {
        $items = [];
        $nights = $reservation->check_in_date->diffInDays($reservation->check_out_date);
        
        // Room charge
        $items[] = [
            'description' => "Room Charge - {$nights} nights",
            'date' => $reservation->check_in_date,
            'quantity' => $nights,
            'unit_price' => $reservation->total_amount / $nights,
            'tax_rate' => 0,
            'total' => $reservation->total_amount,
            'type' => 'charge',
        ];
        
        // Additional charges from meta
        if ($reservation->meta) {
            $meta = $reservation->meta;
            
            if (isset($meta['extra_charges'])) {
                foreach ($meta['extra_charges'] as $charge) {
                    $items[] = [
                        'description' => $charge['description'] ?? 'Extra Charge',
                        'date' => $reservation->check_in_date,
                        'quantity' => 1,
                        'unit_price' => $charge['amount'] ?? 0,
                        'total' => $charge['amount'] ?? 0,
                        'type' => 'charge',
                    ];
                }
            }
        }
        
        return $items;
    }
    
    /**
     * Calculate tax amount.
     */
    protected function calculateTax(float $subtotal, int $hotelId): float
    {
        $taxRate = \App\Models\PropertyTax::where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->where('type', 'percentage')
            ->sum('rate');
        
        if ($taxRate === 0) {
            $taxRate = config('app.default_tax_rate', 0);
        }
        
        return $subtotal * ($taxRate / 100);
    }
    
    /**
     * Generate unique invoice number.
     */
    protected function generateInvoiceNumber(int $hotelId): string
    {
        $prefix = \App\Models\SystemSetting::get('billing.invoice_prefix', 'INV');
        $year = now()->format('Y');
        
        // Get last invoice number for this hotel
        $lastInvoice = Invoice::where('hotel_id', $hotelId)
            ->whereYear('created_at', now()->year)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastInvoice) {
            // Extract number from last invoice
            preg_match('/(\d+)$/', $lastInvoice->invoice_number, $matches);
            $nextNumber = isset($matches[1]) ? (int) $matches[1] + 1 : 1;
        } else {
            $nextNumber = 1;
        }
        
        return "{$prefix}/{$year}/" . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Send invoice to guest.
     */
    public function sendInvoice(Invoice $invoice, string $email): bool
    {
        // TODO: Implement email sending
        $invoice->update([
            'sent_at' => now(),
            'status' => 'sent',
        ]);
        
        return true;
    }
    
    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(Invoice $invoice): void
    {
        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
            'balance' => 0,
        ]);
    }
    
    /**
     * Void invoice.
     */
    public function voidInvoice(Invoice $invoice): void
    {
        $invoice->update([
            'status' => 'cancelled',
        ]);
    }
    
    /**
     * Get overdue invoices.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Invoice>
     */
    public function getOverdueInvoices(int $hotelId)
    {
        return Invoice::where('hotel_id', $hotelId)
            ->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('due_date', '<', now())
            ->orderBy('due_date')
            ->get();
    }
    
    /**
     * Get invoice statistics.
     */
    public function getStatistics(int $hotelId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = Invoice::where('hotel_id', $hotelId);
        
        if ($startDate) {
            $query->where('invoice_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('invoice_date', '<=', $endDate);
        }
        
        $total = $query->count();
        $paid = (clone $query)->where('status', 'paid')->count();
        $pending = (clone $query)->where('status', 'draft')->count();
        $sent = (clone $query)->where('status', 'sent')->count();
        $overdue = (clone $query)->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('due_date', '<', now())
            ->count();
        
        $totalRevenue = (clone $query)->where('status', 'paid')->sum('paid_amount');
        $outstanding = (clone $query)->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->sum('balance');
        
        return [
            'total_invoices' => $total,
            'paid' => $paid,
            'pending' => $pending,
            'sent' => $sent,
            'overdue' => $overdue,
            'total_revenue' => $totalRevenue,
            'outstanding' => $outstanding,
        ];
    }
}
