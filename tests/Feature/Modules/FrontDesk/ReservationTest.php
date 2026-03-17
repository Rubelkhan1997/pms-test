<?php

namespace Tests\Feature\Modules\FrontDesk;

use App\Models\User;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Models\Room;
use App\Modules\Guest\Models\GuestProfile;
use App\Modules\FrontDesk\Enums\ReservationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;
    
    #[Test]
    public function front_desk_can_view_reservations_list(): void
    {
        // Arrange
        $user = $this->createUserWithRole('front_desk');
        Reservation::factory()->count(5)->create();
        
        // Act
        $response = $this->actingAs($user)
            ->get(route('front-desk.reservations.index'));
        
        // Assert
        $response->assertStatus(200)
            ->assertViewIs('front-desk.reservations.index')
            ->assertViewHas('reservations');
    }
    
    #[Test]
    public function front_desk_can_create_reservation(): void
    {
        // Arrange
        $user = $this->createUserWithRole('front_desk');
        $room = Room::factory()->create();
        $guestProfile = GuestProfile::factory()->create();
        
        $reservationData = [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'check_in_date' => now()->addDays(7)->toDateString(),
            'check_out_date' => now()->addDays(10)->toDateString(),
            'adults' => 2,
            'children' => 0,
            'total_amount' => 500.00,
            'status' => ReservationStatus::Confirmed->value,
        ];
        
        // Act
        $response = $this->actingAs($user)
            ->post(route('front-desk.reservations.store'), $reservationData);
        
        // Assert
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        
        $this->assertDatabaseHas('reservations', [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'adults' => 2,
            'status' => ReservationStatus::Confirmed->value,
        ]);
    }
    
    #[Test]
    public function reservation_requires_valid_check_out_date(): void
    {
        // Arrange
        $user = $this->createUserWithRole('front_desk');
        $room = Room::factory()->create();
        $guestProfile = GuestProfile::factory()->create();
        
        $reservationData = [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'check_in_date' => now()->addDays(10)->toDateString(),
            'check_out_date' => now()->addDays(5)->toDateString(), // Before check-in
            'adults' => 2,
            'total_amount' => 500.00,
        ];
        
        // Act
        $response = $this->actingAs($user)
            ->post(route('front-desk.reservations.store'), $reservationData);
        
        // Assert
        $response->assertSessionHasErrors('check_out_date');
    }
    
    #[Test]
    public function unauthorized_user_cannot_create_reservation(): void
    {
        // Arrange
        $user = User::factory()->create();
        // No role assigned
        
        // Act & Assert
        $this->actingAs($user)
            ->post(route('front-desk.reservations.store'), [])
            ->assertForbidden();
    }
    
    #[Test]
    public function it_generates_unique_reference_number(): void
    {
        // Arrange
        $user = $this->createUserWithRole('front_desk');
        $room = Room::factory()->create();
        $guestProfile = GuestProfile::factory()->create();
        
        $reservationData = [
            'room_id' => $room->id,
            'guest_profile_id' => $guestProfile->id,
            'check_in_date' => now()->addDays(7)->toDateString(),
            'check_out_date' => now()->addDays(10)->toDateString(),
            'adults' => 2,
            'total_amount' => 500.00,
        ];
        
        // Act
        $this->actingAs($user)
            ->post(route('front-desk.reservations.store'), $reservationData);
        
        // Assert
        $reservation = Reservation::latest()->first();
        expect($reservation->reference)
            ->toBeString()
            ->toHaveLength(17); // e.g., "HTL-20260317-A1B2"
    }
}
