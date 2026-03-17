<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Hr\Models\ShiftSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<ShiftSchedule>
 */
class ShiftScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hotel_id' => HotelFactory::new(),
            'employee_id' => Employee::factory(),
            'shift_date' => fake()->dateTimeBetween('-1 week', '+2 weeks'),
            'start_time' => fake()->randomElement(['06:00', '08:00', '14:00', '22:00']),
            'end_time' => fake()->randomElement(['14:00', '16:00', '22:00', '06:00']),
            'status' => fake()->randomElement(['scheduled', 'completed', 'cancelled']),
        ];
    }
    
    public function morning(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => '06:00',
            'end_time' => '14:00',
        ]);
    }
    
    public function evening(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => '14:00',
            'end_time' => '22:00',
        ]);
    }
    
    public function night(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => '22:00',
            'end_time' => '06:00',
        ]);
    }
}
