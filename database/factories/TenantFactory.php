<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = $this->faker->company();
        $slug = Str::slug($name);

        return [
            'name'          => $name,
            'slug'          => $slug,
            'domain'        => $slug . '.pms.test',
            'database'      => 'pms_' . Str::replace('-', '_', $slug),
            'status'        => 'active',
        ];
    }

    public function suspended(): static
    {
        return $this->state(['status' => 'suspended']);
    }

    public function trial(int $days = 14): static
    {
        return $this->state([
            'status'        => 'trial',
            'trial_ends_at' => now()->addDays($days),
        ]);
    }

    public function expiredTrial(): static
    {
        return $this->state([
            'status'        => 'trial',
            'trial_ends_at' => now()->subDay(),
        ]);
    }
}
