<?php

namespace Database\Factories;

use App\Models\Major;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Major>
 */
class MajorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate unique combinations using faker to avoid conflicts
        return [
            'major_name' => $this->faker->words(3, true) . ' ' . $this->faker->randomNumber(3),
            'major_code' => $this->faker->regexify('[A-Z]{3}[0-9]{2}'),

            // Commented preserved field from Training - uncomment when needed
            // 'description' => $this->faker->paragraph(),
        ];
    }
    /**
     * Create a specific major.
     */
    public function withName(string $name, string $code): static
    {
        return $this->state(fn(array $attributes) => [
            'major_name' => $name,
            'major_code' => $code,
        ]);
    }
}
