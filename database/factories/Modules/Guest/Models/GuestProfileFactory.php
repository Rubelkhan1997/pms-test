<?php

declare(strict_types=1);

namespace Database\Factories\Modules\Guest\Models;

use App\Models\Hotel;
use App\Models\User;
use App\Modules\Guest\Models\GuestProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<GuestProfile>
 */
class GuestProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory(),
            'agent_id' => null,
            'created_by' => User::factory(),
            'reference' => 'GST-' . strtoupper(fake()->unique()->randomNumber(6)),
            'status' => 'active',
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'date_of_birth' => fake()->optional()->dateTimeBetween('-80 years', '-18 years'),
            'meta' => null,
        ];
    }

    /**
     * Indicate that the guest is VIP.
     */
    public function vip(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => ['vip' => true, 'tier' => 'gold'],
        ]);
    }

    /**
     * Indicate that the guest is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
