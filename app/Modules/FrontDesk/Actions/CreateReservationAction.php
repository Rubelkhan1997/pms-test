<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

use App\Modules\FrontDesk\Enums\ReservationStatus;
use App\Modules\FrontDesk\Models\Reservation;
use App\Services\ReferenceGenerator;

class CreateReservationAction
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly ReferenceGenerator $referenceGenerator
    ) {
    }

    /**
     * Persist a new reservation with reference number.
     *
     * @param array<string, mixed> $payload
     */
    public function __invoke(array $payload): Reservation
    {
        // Generate unique reference number
        $reference = $this->generateUniqueReference();
        
        // Set default status if not provided
        $payload['status'] = $payload['status'] ?? ReservationStatus::Draft->value;
        $payload['reference'] = $reference;
        
        // Set default values
        $payload['adults'] = $payload['adults'] ?? 1;
        $payload['children'] = $payload['children'] ?? 0;
        $payload['total_amount'] = $payload['total_amount'] ?? 0;
        $payload['paid_amount'] = $payload['paid_amount'] ?? 0;
        
        return Reservation::query()->create($payload);
    }

    /**
     * Generate a unique reference number.
     */
    private function generateUniqueReference(): string
    {
        $maxAttempts = 10;
        $attempt = 0;
        
        do {
            $reference = $this->referenceGenerator->generate('RES');
            $attempt++;
            
            // Check if reference already exists
            if (!Reservation::where('reference', $reference)->exists()) {
                return $reference;
            }
        } while ($attempt < $maxAttempts);
        
        // Fallback: append random string
        return $this->referenceGenerator->generate('RES') . '-' . strtoupper(\Str::random(4));
    }
}
