<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\FrontDesk;

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

class ReservationWebTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Hotel $hotel;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seedRolesAndPermissions();
        $this->user = $this->createUserWithRole('front_desk');
        $this->hotel = Hotel::factory()->create();
    }

    #[Test]
    public function front_desk_can_view_reservations_list(): void
    {
        // Arrange
        Reservation::factory()->count(5)->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->actingAs($this->user)
            ->get(route('front-desk.reservations.index'));

        // Assert
        $response->assertStatus(200)
            ->assertViewIs('FrontDesk/Reservations/Index')
            ->assertViewHas('reservations');
    }

    #[Test]
    public function front_desk_can_view_single_reservation(): void
    {
        // Arrange
        $reservation = Reservation::factory()->create([
            'hotel_id' => $this->hotel->id,
        ]);

        // Act
        $response = $this->actingAs($this->user)
            ->get(route('front-desk.reservations.show', $reservation->id));

        // Assert
        $response->assertStatus(200)
            ->assertViewIs('FrontDesk/Reservations/Show')
            ->assertViewHas('reservation', function ($viewReservation) use ($reservation) {
                return $viewReservation->id === $reservation->id;
            });
    }

    #[Test]
    public function front_desk_can_create_reservation(): void
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
            'children' => 0,
            'total_amount' => 500.00,
        ];

        // Act
        $response = $this->actingAs($this->user)
            ->post(route('front-desk.reservations.store'), $payload);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('reservations', [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'adults' => 2,
            'status' => ReservationStatus::Draft->value,
        ]);
    }

    #[Test]
    public function reservation_creation_generates_reference_number(): void
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
        $response = $this->actingAs($this->user)
            ->post(route('front-desk.reservations.store'), $payload);

        // Assert
        $reservation = Reservation::latest()->first();
        
        expect($reservation->reference)
            ->toBeString()
            ->toMatch('/^RES-\d{8}-[A-Z0-9]{4}$/');
    }

    #[Test]
    public function front_desk_can_update_reservation(): void
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
        $response = $this->actingAs($this->user)
            ->put(route('front-desk.reservations.update', $reservation->id), $payload);

        // Assert
        $response->assertRedirect();
        
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'adults' => 3,
            'children' => 1,
            'total_amount' => 600.00,
        ]);
    }

    #[Test]
    public function front_desk_can_delete_reservation(): void
    {
        // Arrange
        $reservation = Reservation::factory()->draft()->create([
            'hotel_id' => $this->hotel->id,
        ]);

        // Act
        $response = $this->actingAs($this->user)
            ->delete(route('front-desk.reservations.destroy', $reservation->id));

        // Assert
        $response->assertRedirect();
        
        $this->assertSoftDeleted('reservations', ['id' => $reservation->id]);
    }

    #[Test]
    public function reservation_requires_valid_check_out_date(): void
    {
        // Arrange
        $room = Room::factory()->available()->create(['hotel_id' => $this->hotel->id]);
        $guestProfile = GuestProfile::factory()->create(['hotel_id' => $this->hotel->id]);

        $payload = [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'check_in_date' => now()->addDays(10)->toDateString(),
            'check_out_date' => now()->addDays(5)->toDateString(), // Before check-in
            'adults' => 2,
            'total_amount' => 500.00,
        ];

        // Act
        $response = $this->actingAs($this->user)
            ->post(route('front-desk.reservations.store'), $payload);

        // Assert
        $response->assertSessionHasErrors('check_out_date');
    }

    #[Test]
    public function reservation_requires_valid_check_in_date(): void
    {
        // Arrange
        $room = Room::factory()->available()->create(['hotel_id' => $this->hotel->id]);
        $guestProfile = GuestProfile::factory()->create(['hotel_id' => $this->hotel->id]);

        $payload = [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'check_in_date' => now()->subDays(5)->toDateString(), // In the past
            'check_out_date' => now()->addDays(10)->toDateString(),
            'adults' => 2,
            'total_amount' => 500.00,
        ];

        // Act
        $response = $this->actingAs($this->user)
            ->post(route('front-desk.reservations.store'), $payload);

        // Assert
        $response->assertSessionHasErrors('check_in_date');
    }

    #[Test]
    public function unauthorized_user_cannot_create_reservation(): void
    {
        // Arrange
        $user = User::factory()->create(); // No role assigned

        // Act & Assert
        $this->actingAs($user)
            ->post(route('front-desk.reservations.store'), [])
            ->assertForbidden();
    }

    #[Test]
    public function unauthorized_user_cannot_update_reservation(): void
    {
        // Arrange
        $user = User::factory()->create(); // No role assigned
        $reservation = Reservation::factory()->create(['hotel_id' => $this->hotel->id]);

        // Act & Assert
        $this->actingAs($user)
            ->put(route('front-desk.reservations.update', $reservation->id), [])
            ->assertForbidden();
    }

    #[Test]
    public function unauthorized_user_cannot_delete_reservation(): void
    {
        // Arrange
        $user = User::factory()->create(); // No role assigned
        $reservation = Reservation::factory()->create(['hotel_id' => $this->hotel->id]);

        // Act & Assert
        $this->actingAs($user)
            ->delete(route('front-desk.reservations.destroy', $reservation->id))
            ->assertForbidden();
    }
}
