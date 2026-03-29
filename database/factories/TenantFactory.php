<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();
        $subdomain = strtolower(preg_replace('/[^A-Za-z0-9]/', '-', $name));

        return [
            'name' => $name,
            'company_name' => $name . ' Ltd.',
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'subdomain' => $subdomain . '-' . uniqid(),
            'database_name' => 'tenant_' . $subdomain . '_' . uniqid(),
            'status' => 'pending',
            'country' => fake()->countryCode(),
            'timezone' => fake()->randomElement(['UTC', 'America/New_York', 'Europe/London', 'Asia/Dubai', 'Asia/Bangkok']),
            'currency' => fake()->randomElement(['USD', 'EUR', 'GBP', 'AED', 'THB']),
            'settings' => [],
            'metadata' => [],
            'trial_ends_at' => now()->addDays(14),
        ];
    }

    /**
     * Indicate that the tenant is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'activated_at' => now(),
        ]);
    }

    /**
     * Indicate that the tenant is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
            'suspended_at' => now(),
        ]);
    }

    /**
     * Indicate that the tenant is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }

    /**
     * Indicate that the tenant is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
