<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Hotel;

/**
 * Class ReferenceGenerator
 * 
 * Generates human-readable reference numbers for reservations, guests, etc.
 * Format: {HOTEL_CODE}-{YYYYMMDD}-{RANDOM}
 * 
 * Examples:
 * - HTL-20260317-A1B2
 * - NYC-20260317-X9Y8
 */
class ReferenceGenerator
{
    /**
     * Generate a unique reference number
     */
    public function generate(string $prefix = 'REF'): string
    {
        $hotelCode = $this->getHotelCode();
        $date = now()->format('Ymd');
        $random = $this->generateRandomSegment();
        
        return "{$hotelCode}-{$date}-{$random}";
    }
    
    /**
     * Generate reference with custom prefix
     */
    public function generateWithPrefix(string $prefix): string
    {
        $date = now()->format('Ymd');
        $random = $this->generateRandomSegment();
        
        return "{$prefix}-{$date}-{$random}";
    }
    
    /**
     * Get current hotel code
     */
    private function getHotelCode(): string
    {
        $hotel = currentHotel();
        
        if (!$hotel) {
            return 'SYS';
        }
        
        return strtoupper(substr($hotel->code ?? $hotel->name ?? 'HTL', 0, 3));
    }
    
    /**
     * Generate random 4-character segment
     */
    private function generateRandomSegment(): string
    {
        return strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
    }
    
    /**
     * Check if reference is valid format
     */
    public function isValidFormat(string $reference): bool
    {
        return preg_match('/^[A-Z]{3}-\d{8}-[A-Z0-9]{4}$/', $reference) === 1;
    }
    
    /**
     * Extract date from reference
     */
    public function extractDate(string $reference): ?\Carbon\Carbon
    {
        if (!$this->isValidFormat($reference)) {
            return null;
        }
        
        $parts = explode('-', $reference);
        if (count($parts) !== 3) {
            return null;
        }
        
        try {
            return \Carbon\Carbon::createFromFormat('Ymd', $parts[1]);
        } catch (\Exception $e) {
            return null;
        }
    }
}
