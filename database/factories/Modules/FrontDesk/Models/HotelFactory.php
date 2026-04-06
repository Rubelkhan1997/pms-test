<?php

declare(strict_types=1);

namespace Database\Factories\Modules\FrontDesk\Models;

use App\Modules\FrontDesk\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;

class HotelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Hotel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'     => $this->faker->company(),
            'code'     => strtoupper($this->faker->bothify('HTL####')),
            'timezone' => $this->faker->timezone(),
            'currency' => $this->faker->randomElement(['BDT', 'USD', 'EUR', 'GBP']),
            'email'    => $this->faker->companyEmail(),
            'phone'    => $this->faker->phoneNumber(),
            'address'  => $this->faker->address(),
        ];
    }
}
