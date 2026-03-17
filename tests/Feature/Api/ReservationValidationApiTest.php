<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Hotel;
use App\Models\User;
use App\Modules\FrontDesk\Factories\ReservationFactory;
use App\Modules\FrontDesk\Factories\RoomFactory;
use App\Modules\Guest\Factories\GuestProfileFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReservationValidationApiTest extends TestCase
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
    public function it_validates_adults_minimum_is_one(): void
    {
        // Arrange
        $room = Room::factory()->create(['hotel_id' => $this->hotel->id]);
        $guestProfile = GuestProfile::factory()->create(['hotel_id' => $this->hotel->id]);

        $payload = [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'check_in_date' => now()->addDays(7)->toDateString(),
            'check_out_date' => now()->addDays(10)->toDateString(),
            'adults' => 0,
            'total_amount' => 500.00,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/front-desk/reservations', $payload);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors('adults');
    }

    #[Test]
    public function it_validates_adults_maximum_is_ten(): void
    {
        // Arrange
        $room = Room::factory()->create(['hotel_id' => $this->hotel->id]);
        $guestProfile = GuestProfile::factory()->create(['hotel_id' => $this->hotel->id]);

        $payload = [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'check_in_date' => now()->addDays(7)->toDateString(),
            'check_out_date' => now()->addDays(10)->toDateString(),
            'adults' => 11,
            'total_amount' => 500.00,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/front-desk/reservations', $payload);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors('adults');
    }

    #[Test]
    public function it_validates_children_cannot_be_negative(): void
    {
        // Arrange
        $room = Room::factory()->create(['hotel_id' => $this->hotel->id]);
        $guestProfile = GuestProfile::factory()->create(['hotel_id' => $this->hotel->id]);

        $payload = [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'check_in_date' => now()->addDays(7)->toDateString(),
            'check_out_date' => now()->addDays(10)->toDateString(),
            'adults' => 2,
            'children' => -1,
            'total_amount' => 500.00,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/front-desk/reservations', $payload);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors('children');
    }

    #[Test]
    public function it_validates_total_amount_cannot_be_negative(): void
    {
        // Arrange
        $room = Room::factory()->create(['hotel_id' => $this->hotel->id]);
        $guestProfile = GuestProfile::factory()->create(['hotel_id' => $this->hotel->id]);

        $payload = [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'check_in_date' => now()->addDays(7)->toDateString(),
            'check_out_date' => now()->addDays(10)->toDateString(),
            'adults' => 2,
            'total_amount' => -100,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/front-desk/reservations', $payload);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors('total_amount');
    }

    #[Test]
    public function it_validates_room_id_exists(): void
    {
        // Arrange
        $guestProfile = GuestProfile::factory()->create(['hotel_id' => $this->hotel->id]);

        $payload = [
            'room_id' => 99999,
            'guest_profile_id' => $guestProfile->id,
            'check_in_date' => now()->addDays(7)->toDateString(),
            'check_out_date' => now()->addDays(10)->toDateString(),
            'adults' => 2,
            'total_amount' => 500.00,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/front-desk/reservations', $payload);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors('room_id');
    }

    #[Test]
    public function it_validates_guest_profile_id_exists(): void
    {
        // Arrange
        $room = Room::factory()->create(['hotel_id' => $this->hotel->id]);

        $payload = [
            'room_id' => $room->id,
            'guest_profile_id' => 99999,
            'check_in_date' => now()->addDays(7)->toDateString(),
            'check_out_date' => now()->addDays(10)->toDateString(),
            'adults' => 2,
            'total_amount' => 500.00,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/front-desk/reservations', $payload);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors('guest_profile_id');
    }

    #[Test]
    public function it_validates_check_in_date_cannot_be_in_past(): void
    {
        // Arrange
        $room = Room::factory()->create(['hotel_id' => $this->hotel->id]);
        $guestProfile = GuestProfile::factory()->create(['hotel_id' => $this->hotel->id]);

        $payload = [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'check_in_date' => now()->subDays(5)->toDateString(),
            'check_out_date' => now()->addDays(10)->toDateString(),
            'adults' => 2,
            'total_amount' => 500.00,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/front-desk/reservations', $payload);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors('check_in_date');
    }

    #[Test]
    public function it_validates_status_must_be_valid_enum(): void
    {
        // Arrange
        $room = Room::factory()->create(['hotel_id' => $this->hotel->id]);
        $guestProfile = GuestProfile::factory()->create(['hotel_id' => $this->hotel->id]);

        $payload = [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'check_in_date' => now()->addDays(7)->toDateString(),
            'check_out_date' => now()->addDays(10)->toDateString(),
            'adults' => 2,
            'total_amount' => 500.00,
            'status' => 'invalid_status',
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/front-desk/reservations', $payload);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    #[Test]
    public function it_allows_null_children_field(): void
    {
        // Arrange
        $room = Room::factory()->create(['hotel_id' => $this->hotel->id]);
        $guestProfile = GuestProfile::factory()->create(['hotel_id' => $this->hotel->id]);

        $payload = [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'check_in_date' => now()->addDays(7)->toDateString(),
            'check_out_date' => now()->addDays(10)->toDateString(),
            'adults' => 2,
            'children' => null,
            'total_amount' => 500.00,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/front-desk/reservations', $payload);

        // Assert
        $response->assertCreated();
    }

    #[Test]
    public function it_allows_zero_children(): void
    {
        // Arrange
        $room = Room::factory()->create(['hotel_id' => $this->hotel->id]);
        $guestProfile = GuestProfile::factory()->create(['hotel_id' => $this->hotel->id]);

        $payload = [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'check_in_date' => now()->addDays(7)->toDateString(),
            'check_out_date' => now()->addDays(10)->toDateString(),
            'adults' => 2,
            'children' => 0,
            'total_amount' => 500.00,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/front-desk/reservations', $payload);

        // Assert
        $response->assertCreated();
    }

    #[Test]
    public function it_allows_zero_total_amount(): void
    {
        // Arrange
        $room = Room::factory()->create(['hotel_id' => $this->hotel->id]);
        $guestProfile = GuestProfile::factory()->create(['hotel_id' => $this->hotel->id]);

        $payload = [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'check_in_date' => now()->addDays(7)->toDateString(),
            'check_out_date' => now()->addDays(10)->toDateString(),
            'adults' => 2,
            'total_amount' => 0,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/front-desk/reservations', $payload);

        // Assert
        $response->assertCreated();
    }

    #[Test]
    public function it_validates_same_check_in_and_check_out_date(): void
    {
        // Arrange
        $room = Room::factory()->create(['hotel_id' => $this->hotel->id]);
        $guestProfile = GuestProfile::factory()->create(['hotel_id' => $this->hotel->id]);

        $payload = [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'check_in_date' => now()->addDays(7)->toDateString(),
            'check_out_date' => now()->addDays(7)->toDateString(), // Same as check-in
            'adults' => 2,
            'total_amount' => 500.00,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/front-desk/reservations', $payload);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors('check_out_date');
    }
}
