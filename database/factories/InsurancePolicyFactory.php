<?php

namespace Database\Factories;

use App\Models\InsurancePolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InsurancePolicy>
 */
class InsurancePolicyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsOn = fake()->dateTimeBetween('-1 year', 'now');

        return [
            'policy_number' => 'P-'.fake()->unique()->numerify('######'),
            'holder_name' => fake()->name(),
            'insurer' => fake()->company(),
            'starts_on' => $startsOn->format('Y-m-d'),
            'ends_on' => fake()->dateTimeBetween($startsOn, '+1 year')->format('Y-m-d'),
            'premium' => fake()->randomFloat(2, 50, 3000),
        ];
    }
}
