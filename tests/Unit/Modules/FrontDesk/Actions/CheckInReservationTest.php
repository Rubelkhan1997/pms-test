<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\FrontDesk\Actions;

use App\Modules\FrontDesk\Actions\CheckInReservation;
use App\Modules\FrontDesk\Enums\ReservationStatus;
use App\Modules\FrontDesk\Enums\RoomStatus;
use App\Modules\FrontDesk\Events\ReservationCheckedIn;
use App\Modules\FrontDesk\Factories\ReservationFactory;
use App\Modules\FrontDesk\Factories\RoomFactory;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckInReservationTest extends TestCase
{
    use RefreshDatabase;

    private CheckInReservation $action;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->action = new CheckInReservation();
        Event::fake();
    }

    #[Test]
    public function it_checks_in_confirmed_reservation(): void
    {
        // Arrange
        $room = Room::factory()->available()->create();
        $reservation = Reservation::factory()->confirmed()->create([
            'room_id' => $room->id,
        ]);

        // Act
        $result = $this->action->execute($reservation);

        // Assert
        expect($result->status)->toBe(ReservationStatus::CheckedIn)
            ->and($result->actual_check_in)->toBeInstanceOf(\Carbon\Carbon::class)
            ->and($result->room->fresh()->status)->toBe(RoomStatus::Occupied);
    }

    #[Test]
    public function it_throws_exception_for_non_confirmed_reservation(): void
    {
        // Arrange
        $reservation = Reservation::factory()->draft()->create();

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only confirmed reservations can be checked in');
        
        $this->action->execute($reservation);
    }

    #[Test]
    public function it_throws_exception_for_already_checked_in_reservation(): void
    {
        // Arrange
        $reservation = Reservation::factory()->checkedIn()->create();

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        
        $this->action->execute($reservation);
    }

    #[Test]
    public function it_throws_exception_for_cancelled_reservation(): void
    {
        // Arrange
        $reservation = Reservation::factory()->cancelled()->create();

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        
        $this->action->execute($reservation);
    }

    #[Test]
    public function it_dispatches_reservation_checked_in_event(): void
    {
        // Arrange
        $room = Room::factory()->available()->create();
        $reservation = Reservation::factory()->confirmed()->create([
            'room_id' => $room->id,
        ]);

        // Act
        $this->action->execute($reservation);

        // Assert
        Event::assertDispatched(ReservationCheckedIn::class, function ($event) use ($reservation) {
            return $event->reservation->id === $reservation->id;
        });
    }

    #[Test]
    public function it_updates_room_status_to_occupied(): void
    {
        // Arrange
        $room = Room::factory()->available()->create();
        $reservation = Reservation::factory()->confirmed()->create([
            'room_id' => $room->id,
        ]);

        // Act
        $this->action->execute($reservation);

        // Assert
        expect($room->fresh()->status)->toBe(RoomStatus::Occupied);
    }

    #[Test]
    public function it_returns_fresh_reservation_with_relationships(): void
    {
        // Arrange
        $room = Room::factory()->available()->create();
        $reservation = Reservation::factory()->confirmed()->create([
            'room_id' => $room->id,
        ]);

        // Act
        $result = $this->action->execute($reservation);

        // Assert
        expect($result->relationLoaded('room'))->toBeTrue()
            ->and($result->relationLoaded('guestProfile'))->toBeTrue();
    }
}
