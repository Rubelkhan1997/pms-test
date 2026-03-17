<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Modules\FrontDesk\Services\PricingService;
use App\Modules\FrontDesk\Services\AvailabilityService;
use App\Modules\FrontDesk\Services\RoomAssignmentService;
use App\Modules\FrontDesk\Models\RoomType;
use App\Modules\FrontDesk\Models\RatePlan;
use App\Modules\FrontDesk\Models\Room;
use App\Models\Hotel;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    private PricingService $pricingService;
    private Hotel $hotel;
    private RoomType $roomType;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->pricingService = app(PricingService::class);
        $this->hotel = Hotel::factory()->create(['is_active' => true]);
        
        $this->roomType = RoomType::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Standard Room',
            'code' => 'STD',
            'base_rate' => 100.00,
            'max_occupancy' => 3,
            'is_active' => true,
        ]);
        
        RatePlan::create([
            'hotel_id' => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'name' => 'Best Available Rate',
            'code' => 'BAR',
            'base_rate' => 100.00,
            'is_active' => true,
        ]);
    }
    
    #[Test]
    public function it_calculates_base_rate_correctly(): void
    {
        $checkIn = Carbon::now()->addDays(7);
        $checkOut = $checkIn->copy()->addDays(3);
        
        $pricing = $this->pricingService->calculateStayPrice(
            $this->roomType->id,
            null,
            $checkIn,
            $checkOut,
            2,
            0
        );
        
        expect($pricing['base_total'])->toBe(300.00) // 3 nights × $100
            ->and($pricing['nights'])->toBe(3);
    }
    
    #[Test]
    public function it_applies_weekly_discount(): void
    {
        $checkIn = Carbon::now()->addDays(7);
        $checkOut = $checkIn->copy()->addDays(7); // 7 nights
        
        $pricing = $this->pricingService->calculateStayPrice(
            $this->roomType->id,
            null,
            $checkIn,
            $checkOut,
            2,
            0
        );
        
        // 7 nights × $100 = $700, 10% discount = $70
        expect($pricing['adjustments']['total'])->toBeLessThan(0);
    }
    
    #[Test]
    public function it_calculates_extra_adult_charges(): void
    {
        $checkIn = Carbon::now()->addDays(7);
        $checkOut = $checkIn->copy()->addDays(3);
        
        $pricing = $this->pricingService->calculateStayPrice(
            $this->roomType->id,
            null,
            $checkIn,
            $checkOut,
            4, // 2 extra adults
            0
        );
        
        expect($pricing['adjustments']['total'])->toBeGreaterThan(0);
    }
}
