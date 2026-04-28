<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'slug' => $this->faker->slug(),
            'property_limit' => $this->faker->numberBetween(1, 10),
            'room_limit' => $this->faker->numberBetween(10, 100),
            'price_monthly' => $this->faker->randomFloat(2, 10, 100),
            'price_annual' => $this->faker->randomFloat(2, 100, 1000),
            'trial_enabled' => $this->faker->boolean(),
            'trial_days' => $this->faker->numberBetween(7, 30),
            'modules_included' => $this->faker->words(3),
            'is_active' => true,
        ];
    }
}
