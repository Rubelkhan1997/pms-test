<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Hotel;
use App\Models\User;
use App\Modules\FrontDesk\Enums\ReservationStatus;
use App\Modules\FrontDesk\Enums\RoomStatus;
use App\Modules\FrontDesk\Factories\ReservationFactory;
use App\Modules\FrontDesk\Factories\RoomFactory;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReservationWorkflowApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Hotel $hotel;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seedRolesAndPermissions();
        $this->user = $this->createUserWithRole('front_desk');
        $this->token = $this->user->createToken('api-token')->plainTextToken;
        $this->hotel = Hotel::factory()->create();
    }

    #[Test]
    public function front_desk_can_check_in_confirmed_reservation(): void
    {
        // Arrange
        $room = Room::factory()->available()->create(['hotel_id' => $this->hotel->id]);
        $reservation = Reservation::factory()->confirmed()->create([
            'hotel_id' => $this->hotel->id,
            'room_id' => $room->id,
        ]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/v1/front-desk/reservations/{$reservation->id}/check-in");

        // Assert
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $reservation->id,
                    'status' => 'checked_in',
                ]
            ]);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => ReservationStatus::CheckedIn->value,
        ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => RoomStatus::Occupied->value,
        ]);
    }

    #[Test]
    public function check_in_fails_for_draft_reservation(): void
    {
        // Arrange
        $reservation = Reservation::factory()->draft()->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/v1/front-desk/reservations/{$reservation->id}/check-in");

        // Assert
        $response->assertUnprocessable()
            ->assertJson([
                'message' => 'Only confirmed reservations can be checked in',
            ]);
    }

    #[Test]
    public function check_in_fails_for_cancelled_reservation(): void
    {
        // Arrange
        $reservation = Reservation::factory()->cancelled()->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/v1/front-desk/reservations/{$reservation->id}/check-in");

        // Assert
        $response->assertUnprocessable();
    }

    #[Test]
    public function front_desk_can_check_out_checked_in_reservation(): void
    {
        // Arrange
        $room = Room::factory()->occupied()->create(['hotel_id' => $this->hotel->id]);
        $reservation = Reservation::factory()->checkedIn()->create([
            'hotel_id' => $this->hotel->id,
            'room_id' => $room->id,
        ]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/v1/front-desk/reservations/{$reservation->id}/check-out");

        // Assert
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $reservation->id,
                    'status' => 'checked_out',
                ]
            ]);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => ReservationStatus::CheckedOut->value,
        ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => RoomStatus::Dirty->value,
        ]);
    }

    #[Test]
    public function check_out_fails_for_confirmed_reservation(): void
    {
        // Arrange
        $reservation = Reservation::factory()->confirmed()->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/v1/front-desk/reservations/{$reservation->id}/check-out");

        // Assert
        $response->assertUnprocessable()
            ->assertJson([
                'message' => 'Only checked-in reservations can be checked out',
            ]);
    }

    #[Test]
    public function front_desk_can_cancel_confirmed_reservation(): void
    {
        // Arrange
        $reservation = Reservation::factory()->confirmed()->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/v1/front-desk/reservations/{$reservation->id}/cancel");

        // Assert
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $reservation->id,
                    'status' => 'cancelled',
                ]
            ]);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => ReservationStatus::Cancelled->value,
        ]);
    }

    #[Test]
    public function front_desk_can_cancel_draft_reservation(): void
    {
        // Arrange
        $reservation = Reservation::factory()->draft()->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/v1/front-desk/reservations/{$reservation->id}/cancel");

        // Assert
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'status' => 'cancelled',
                ]
            ]);
    }

    #[Test]
    public function cancel_fails_for_checked_in_reservation(): void
    {
        // Arrange
        $reservation = Reservation::factory()->checkedIn()->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/v1/front-desk/reservations/{$reservation->id}/cancel");

        // Assert
        $response->assertUnprocessable();
    }

    #[Test]
    public function it_returns_todays_arrivals(): void
    {
        // Arrange
        $today = now()->toDateString();
        
        Reservation::factory()->count(3)->create([
            'hotel_id' => $this->hotel->id,
            'check_in_date' => $today,
            'status' => ReservationStatus::Confirmed,
        ]);
        
        Reservation::factory()->count(2)->create([
            'hotel_id' => $this->hotel->id,
            'check_in_date' => now()->addDays(5),
        ]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/front-desk/reservations/reports/arrivals');

        // Assert
        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function it_returns_todays_departures(): void
    {
        // Arrange
        $today = now()->toDateString();
        
        Reservation::factory()->count(2)->create([
            'hotel_id' => $this->hotel->id,
            'check_out_date' => $today,
            'status' => ReservationStatus::CheckedIn,
        ]);
        
        Reservation::factory()->count(3)->create([
            'hotel_id' => $this->hotel->id,
            'check_out_date' => now()->addDays(5),
        ]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/front-desk/reservations/reports/departures');

        // Assert
        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function it_returns_in_house_guests(): void
    {
        // Arrange
        Reservation::factory()->count(4)->checkedIn()->create(['hotel_id' => $this->hotel->id]);
        Reservation::factory()->count(3)->confirmed()->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/front-desk/reservations/reports/in-house');

        // Assert
        $response->assertOk()
            ->assertJsonCount(4, 'data');
    }
}
