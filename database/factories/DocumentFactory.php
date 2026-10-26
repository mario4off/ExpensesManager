<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $area = fake()->randomElement(['invoices', 'insurance_policies']);

        return [
            'area' => $area,
            'path' => $area.'/'.Str::uuid().'.pdf',
            'original_name' => fake()->word().'.pdf',
        ];
    }
}
