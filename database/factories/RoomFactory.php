<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Hotel;
use App\Modules\FrontDesk\Enums\RoomStatus;
use App\Modules\FrontDesk\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Room>
 */
class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory(),
            'number' => fake()->unique()->numerify('###'),
            'floor' => fake()->numberBetween(1, 10),
            'type' => fake()->randomElement(['single', 'double', 'twin', 'suite', 'deluxe']),
            'status' => fake()->randomElement(RoomStatus::cases()),
            'base_rate' => fake()->randomFloat(2, 50, 500),
        ];
    }

    /**
     * Indicate that the room is available.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RoomStatus::Available,
        ]);
    }

    /**
     * Indicate that the room is occupied.
     */
    public function occupied(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RoomStatus::Occupied,
        ]);
    }

    /**
     * Indicate that the room is dirty.
     */
    public function dirty(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RoomStatus::Dirty,
        ]);
    }

    /**
     * Indicate that the room is out of order.
     */
    public function outOfOrder(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RoomStatus::OutOfOrder,
        ]);
    }
}
