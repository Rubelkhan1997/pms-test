<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\FrontDesk\Actions;

use App\Modules\FrontDesk\Actions\CheckInReservation;
use App\Modules\FrontDesk\Actions\CheckOutReservation;
use App\Modules\FrontDesk\Actions\CreateReservationAction;
use App\Modules\FrontDesk\Enums\ReservationStatus;
use App\Modules\FrontDesk\Enums\RoomStatus;
use App\Modules\FrontDesk\Events\ReservationCheckedIn;
use App\Modules\FrontDesk\Events\ReservationCheckedOut;
use App\Modules\FrontDesk\Factories\ReservationFactory;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Models\Room;
use App\Services\ReferenceGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateReservationActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateReservationAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->action = new CreateReservationAction(
            new ReferenceGenerator()
        );
    }

    #[Test]
    public function it_creates_reservation_with_reference_number(): void
    {
        // Arrange
        $payload = [
            'hotel_id' => 1,
            'room_id' => 1,
            'guest_profile_id' => 1,
            'check_in_date' => now()->addDays(7),
            'check_out_date' => now()->addDays(10),
            'adults' => 2,
            'total_amount' => 500.00,
        ];

        // Act
        $reservation = ($this->action)($payload);

        // Assert
        expect($reservation)->toBeInstanceOf(Reservation::class)
            ->and($reservation->reference)->toBeString()
            ->and($reservation->reference)->toMatch('/^RES-\d{8}-[A-Z0-9]{4}$/')
            ->and($reservation->status)->toBe(ReservationStatus::Draft);
    }

    #[Test]
    public function it_sets_default_status_to_draft(): void
    {
        // Arrange
        $payload = [
            'hotel_id' => 1,
            'room_id' => 1,
            'guest_profile_id' => 1,
            'check_in_date' => now()->addDays(7),
            'check_out_date' => now()->addDays(10),
            'adults' => 2,
            'total_amount' => 500.00,
        ];

        // Act
        $reservation = ($this->action)($payload);

        // Assert
        expect($reservation->status)->toBe(ReservationStatus::Draft);
    }

    #[Test]
    public function it_uses_provided_status(): void
    {
        // Arrange
        $payload = [
            'hotel_id' => 1,
            'room_id' => 1,
            'guest_profile_id' => 1,
            'check_in_date' => now()->addDays(7),
            'check_out_date' => now()->addDays(10),
            'adults' => 2,
            'total_amount' => 500.00,
            'status' => ReservationStatus::Confirmed->value,
        ];

        // Act
        $reservation = ($this->action)($payload);

        // Assert
        expect($reservation->status)->toBe(ReservationStatus::Confirmed);
    }

    #[Test]
    public function it_sets_default_adults_to_one(): void
    {
        // Arrange
        $payload = [
            'hotel_id' => 1,
            'room_id' => 1,
            'guest_profile_id' => 1,
            'check_in_date' => now()->addDays(7),
            'check_out_date' => now()->addDays(10),
            'total_amount' => 500.00,
        ];

        // Act
        $reservation = ($this->action)($payload);

        // Assert
        expect($reservation->adults)->toBe(1);
    }

    #[Test]
    public function it_sets_default_children_to_zero(): void
    {
        // Arrange
        $payload = [
            'hotel_id' => 1,
            'room_id' => 1,
            'guest_profile_id' => 1,
            'check_in_date' => now()->addDays(7),
            'check_out_date' => now()->addDays(10),
            'adults' => 2,
            'total_amount' => 500.00,
        ];

        // Act
        $reservation = ($this->action)($payload);

        // Assert
        expect($reservation->children)->toBe(0);
    }

    #[Test]
    public function it_sets_default_total_amount_to_zero(): void
    {
        // Arrange
        $payload = [
            'hotel_id' => 1,
            'room_id' => 1,
            'guest_profile_id' => 1,
            'check_in_date' => now()->addDays(7),
            'check_out_date' => now()->addDays(10),
            'adults' => 2,
        ];

        // Act
        $reservation = ($this->action)($payload);

        // Assert
        expect($reservation->total_amount)->toBe(0.0);
    }
}
