<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Hr\Models\Payroll;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Payroll>
 */
class PayrollFactory extends Factory
{
    public function definition(): array
    {
        $grossAmount = fake()->randomFloat(2, 2000, 10000);
        $deductionAmount = $grossAmount * fake()->randomFloat(2, 0.05, 0.15);
        
        return [
            'hotel_id' => HotelFactory::new(),
            'employee_id' => Employee::factory(),
            'period_start' => fake()->dateTimeBetween('-2 months', '-1 month'),
            'period_end' => fake()->dateTimeBetween('-1 month', 'now'),
            'gross_amount' => $grossAmount,
            'deduction_amount' => $deductionAmount,
            'net_amount' => $grossAmount - $deductionAmount,
            'status' => fake()->randomElement(['draft', 'approved', 'paid']),
            'paid_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
    
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
    
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'paid_at' => null,
        ]);
    }
}
