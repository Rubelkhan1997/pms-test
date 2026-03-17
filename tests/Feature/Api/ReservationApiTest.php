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
use App\Modules\Guest\Factories\GuestProfileFactory;
use App\Modules\Guest\Models\GuestProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReservationApiTest extends TestCase
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
    public function it_returns_unauthenticated_without_token(): void
    {
        // Act & Assert
        $this->getJson('/api/v1/front-desk/reservations')
            ->assertUnauthorized();
    }

    #[Test]
    public function it_returns_paginated_reservations(): void
    {
        // Arrange
        Reservation::factory()->count(15)->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/front-desk/reservations');

        // Assert
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'reference',
                        'status',
                        'check_in_date',
                        'check_out_date',
                        'adults',
                        'total_amount',
                        'created_at',
                    ]
                ],
                'links',
                'meta',
            ])
            ->assertJsonCount(15, 'data');
    }

    #[Test]
    public function it_returns_single_reservation(): void
    {
        // Arrange
        $reservation = Reservation::factory()->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/v1/front-desk/reservations/{$reservation->id}");

        // Assert
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $reservation->id,
                    'reference' => $reservation->reference,
                ]
            ]);
    }

    #[Test]
    public function it_returns_404_for_non_existent_reservation(): void
    {
        // Act & Assert
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/front-desk/reservations/999')
            ->assertNotFound();
    }

    #[Test]
    public function it_creates_reservation_via_api(): void
    {
        // Arrange
        $room = Room::factory()->available()->create(['hotel_id' => $this->hotel->id]);
        $guestProfile = GuestProfile::factory()->create(['hotel_id' => $this->hotel->id]);

        $payload = [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'check_in_date' => now()->addDays(7)->toDateString(),
            'check_out_date' => now()->addDays(10)->toDateString(),
            'adults' => 2,
            'total_amount' => 500.00,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/front-desk/reservations', $payload);

        // Assert
        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'reference',
                    'status',
                    'check_in_date',
                    'check_out_date',
                ]
            ]);

        $this->assertDatabaseHas('reservations', [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'adults' => 2,
        ]);
    }

    #[Test]
    public function it_validates_required_fields_on_create(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/front-desk/reservations', []);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'room_id',
                'guest_profile_id',
                'check_in_date',
                'check_out_date',
                'total_amount',
            ]);
    }

    #[Test]
    public function it_validates_check_out_date_is_after_check_in(): void
    {
        // Arrange
        $room = Room::factory()->available()->create(['hotel_id' => $this->hotel->id]);
        $guestProfile = GuestProfile::factory()->create(['hotel_id' => $this->hotel->id]);

        $payload = [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'check_in_date' => now()->addDays(10)->toDateString(),
            'check_out_date' => now()->addDays(5)->toDateString(),
            'adults' => 2,
            'total_amount' => 500.00,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/front-desk/reservations', $payload);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors('check_out_date');
    }

    #[Test]
    public function it_updates_reservation_via_api(): void
    {
        // Arrange
        $reservation = Reservation::factory()->draft()->create([
            'hotel_id' => $this->hotel->id,
            'adults' => 2,
        ]);

        $payload = [
            'adults' => 3,
            'children' => 1,
            'total_amount' => 600.00,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson("/api/v1/front-desk/reservations/{$reservation->id}", $payload);

        // Assert
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $reservation->id,
                    'adults' => 3,
                ]
            ]);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'adults' => 3,
            'children' => 1,
        ]);
    }

    #[Test]
    public function it_deletes_reservation_via_api(): void
    {
        // Arrange
        $reservation = Reservation::factory()->draft()->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->deleteJson("/api/v1/front-desk/reservations/{$reservation->id}");

        // Assert
        $response->assertOk()
            ->assertJson([
                'message' => 'Reservation deleted successfully.',
            ]);

        $this->assertSoftDeleted('reservations', ['id' => $reservation->id]);
    }

    #[Test]
    public function it_filters_reservations_by_status(): void
    {
        // Arrange
        Reservation::factory()->count(5)->confirmed()->create(['hotel_id' => $this->hotel->id]);
        Reservation::factory()->count(3)->checkedIn()->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/front-desk/reservations?status=confirmed');

        // Assert
        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    #[Test]
    public function it_returns_reservations_with_relationships(): void
    {
        // Arrange
        $reservation = Reservation::factory()->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/v1/front-desk/reservations/{$reservation->id}");

        // Assert
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'room' => ['id', 'number', 'type'],
                    'guest' => ['id', 'first_name', 'last_name'],
                ]
            ]);
    }
}
