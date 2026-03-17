<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Hr\Models\Attendance;
use App\Modules\Hr\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-30 days', 'now');
        $checkIn = \Carbon\Carbon::instance($date)->setTime(fake()->numberBetween(8, 9), fake()->numberBetween(0, 59));
        $checkOut = $checkIn->copy()->setTime(fake()->numberBetween(17, 19), fake()->numberBetween(0, 59));
        
        return [
            'hotel_id' => HotelFactory::new(),
            'employee_id' => Employee::factory(),
            'attendance_date' => $date->format('Y-m-d'),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'status' => fake()->randomElement(['present', 'late', 'half_day', 'absent']),
        ];
    }
    
    public function present(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'present',
        ]);
    }
    
    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'late',
            'check_in' => \Carbon\Carbon::now()->setTime(fake()->numberBetween(9, 11), fake()->numberBetween(0, 59)),
        ]);
    }
}
