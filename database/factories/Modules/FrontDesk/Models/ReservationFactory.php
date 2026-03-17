<?php

declare(strict_types=1);

namespace Database\Factories\Modules\FrontDesk\Models;

use App\Models\Hotel;
use App\Models\User;
use App\Modules\FrontDesk\Enums\ReservationStatus;
use App\Modules\FrontDesk\Enums\RoomStatus;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Models\Room;
use App\Modules\Guest\Models\GuestProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory(),
            'room_id' => Room::factory(),
            'guest_profile_id' => GuestProfile::factory(),
            'created_by' => User::factory(),
            'reference' => 'RES-' . now()->format('Ymd') . '-' . strtoupper(fake()->unique()->randomNumber(4)),
            'status' => fake()->randomElement(ReservationStatus::cases()),
            'check_in_date' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'check_out_date' => fake()->dateTimeBetween('+31 days', '+60 days'),
            'actual_check_in' => null,
            'actual_check_out' => null,
            'adults' => fake()->numberBetween(1, 4),
            'children' => fake()->numberBetween(0, 2),
            'total_amount' => fake()->randomFloat(2, 100, 1000),
            'paid_amount' => 0,
            'meta' => null,
        ];
    }

    /**
     * Indicate that the reservation is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReservationStatus::Confirmed,
        ]);
    }

    /**
     * Indicate that the reservation is checked in.
     */
    public function checkedIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReservationStatus::CheckedIn,
            'actual_check_in' => now(),
        ]);
    }

    /**
     * Indicate that the reservation is checked out.
     */
    public function checkedOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReservationStatus::CheckedOut,
            'actual_check_in' => now()->subDays(3),
            'actual_check_out' => now(),
        ]);
    }

    /**
     * Indicate that the reservation is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReservationStatus::Cancelled,
        ]);
    }

    /**
     * Indicate that the reservation is draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReservationStatus::Draft,
        ]);
    }

    /**
     * Indicate that the reservation has paid amount.
     */
    public function withPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => $attributes['total_amount'] * 0.5,
        ]);
    }

    /**
     * Indicate that the reservation is fully paid.
     */
    public function fullyPaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => $attributes['total_amount'],
        ]);
    }
}
