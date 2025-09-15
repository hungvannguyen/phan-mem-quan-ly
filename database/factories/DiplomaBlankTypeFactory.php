<?php

namespace Database\Factories;

use App\Models\DiplomaBlankType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiplomaBlankType>
 */
class DiplomaBlankTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate unique names to avoid conflicts when called multiple times
        return [
            'type_name' => $this->faker->words(3, true) . ' ' . $this->faker->randomNumber(3),
            'prefix' => $this->faker->regexify('[A-Z]{2,3}'),
        ];
    }

    /**
     * Create university degree type.
     */
    public function university(): static
    {
        return $this->state(fn(array $attributes) => [
            'type_name' => 'Bằng tốt nghiệp Đại học',
            'prefix' => 'DH',
        ]);
    }

    /**
     * Create college degree type.
     */
    public function college(): static
    {
        return $this->state(fn(array $attributes) => [
            'type_name' => 'Bằng tốt nghiệp Cao đẳng',
            'prefix' => 'CD',
        ]);
    }
}