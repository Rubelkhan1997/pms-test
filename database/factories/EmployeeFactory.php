<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Hr\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hotel_id' => HotelFactory::new(),
            'user_id' => null,
            'created_by' => UserFactory::new(),
            'reference' => 'EMP-' . strtoupper(fake()->unique()->randomNumber(6)),
            'status' => fake()->randomElement(['active', 'inactive', 'terminated']),
            'department' => fake()->randomElement(['Front Office', 'Housekeeping', 'F&B', 'Maintenance', 'HR', 'Accounting']),
            'scheduled_at' => fake()->optional()->dateTimeBetween('-1 year', '+1 year'),
            'meta' => [
                'employee_number' => fake()->unique()->numerify('EMP-####'),
                'hire_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            ],
        ];
    }
    
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }
    
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
