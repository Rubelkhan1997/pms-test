<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\FrontDesk\Actions;

use App\Modules\FrontDesk\Actions\CheckOutReservation;
use App\Modules\FrontDesk\Enums\ReservationStatus;
use App\Modules\FrontDesk\Enums\RoomStatus;
use App\Modules\FrontDesk\Events\ReservationCheckedOut;
use App\Modules\FrontDesk\Factories\ReservationFactory;
use App\Modules\FrontDesk\Factories\RoomFactory;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckOutReservationTest extends TestCase
{
    use RefreshDatabase;

    private CheckOutReservation $action;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->action = new CheckOutReservation();
        Event::fake();
    }

    #[Test]
    public function it_checks_out_checked_in_reservation(): void
    {
        // Arrange
        $room = Room::factory()->occupied()->create();
        $reservation = Reservation::factory()->checkedIn()->create([
            'room_id' => $room->id,
        ]);

        // Act
        $result = $this->action->execute($reservation);

        // Assert
        expect($result->status)->toBe(ReservationStatus::CheckedOut)
            ->and($result->actual_check_out)->toBeInstanceOf(\Carbon\Carbon::class)
            ->and($result->room->fresh()->status)->toBe(RoomStatus::Dirty);
    }

    #[Test]
    public function it_throws_exception_for_non_checked_in_reservation(): void
    {
        // Arrange
        $reservation = Reservation::factory()->confirmed()->create();

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only checked-in reservations can be checked out');
        
        $this->action->execute($reservation);
    }

    #[Test]
    public function it_throws_exception_for_draft_reservation(): void
    {
        // Arrange
        $reservation = Reservation::factory()->draft()->create();

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        
        $this->action->execute($reservation);
    }

    #[Test]
    public function it_throws_exception_for_checked_out_reservation(): void
    {
        // Arrange
        $reservation = Reservation::factory()->checkedOut()->create();

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        
        $this->action->execute($reservation);
    }

    #[Test]
    public function it_dispatches_reservation_checked_out_event(): void
    {
        // Arrange
        $room = Room::factory()->occupied()->create();
        $reservation = Reservation::factory()->checkedIn()->create([
            'room_id' => $room->id,
        ]);

        // Act
        $this->action->execute($reservation);

        // Assert
        Event::assertDispatched(ReservationCheckedOut::class, function ($event) use ($reservation) {
            return $event->reservation->id === $reservation->id;
        });
    }

    #[Test]
    public function it_updates_room_status_to_dirty(): void
    {
        // Arrange
        $room = Room::factory()->occupied()->create();
        $reservation = Reservation::factory()->checkedIn()->create([
            'room_id' => $room->id,
        ]);

        // Act
        $this->action->execute($reservation);

        // Assert
        expect($room->fresh()->status)->toBe(RoomStatus::Dirty);
    }

    #[Test]
    public function it_returns_fresh_reservation_with_relationships(): void
    {
        // Arrange
        $room = Room::factory()->occupied()->create();
        $reservation = Reservation::factory()->checkedIn()->create([
            'room_id' => $room->id,
        ]);

        // Act
        $result = $this->action->execute($reservation);

        // Assert
        expect($result->relationLoaded('room'))->toBeTrue()
            ->and($result->relationLoaded('guestProfile'))->toBeTrue();
    }
}
