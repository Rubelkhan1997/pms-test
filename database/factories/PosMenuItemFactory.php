<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Pos\Models\PosMenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<PosMenuItem>
 */
class PosMenuItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hotel_id' => HotelFactory::new(),
            'name' => fake()->words(3, true),
            'category' => fake()->randomElement(['Appetizer', 'Main Course', 'Dessert', 'Beverage', 'Alcohol', 'Side Dish']),
            'price' => fake()->randomFloat(2, 5, 100),
            'is_active' => true,
            'description' => fake()->optional()->sentence(10),
            'meta' => [
                'preparation_time' => fake()->numberBetween(5, 30) . ' minutes',
                'allergens' => fake()->randomElements(['gluten', 'dairy', 'nuts', 'seafood'], fake()->numberBetween(0, 3)),
            ],
        ];
    }
    
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
    
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
