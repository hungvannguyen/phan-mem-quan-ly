<?php

namespace Database\Factories;

use App\Models\DiplomaBlank;
use App\Models\DiplomaBlankType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiplomaBlank>
 */
class DiplomaBlankFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'serial_number' => $this->faker->unique()->regexify('[A-Z]{2}[0-9]{8}'),
            'type_id' => DiplomaBlankType::factory(),
            'status' => $this->faker->randomElement([
                DiplomaBlank::STATUS_IN_STOCK,
                DiplomaBlank::STATUS_ISSUED,
                DiplomaBlank::STATUS_RECALLED,
                DiplomaBlank::STATUS_DAMAGED,
            ]),
            'import_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'issue_date' => null,
            'recall_date' => null,
            'issue_reason' => null,
            'recall_reason' => null,
        ];
    }

    /**
     * Create an available diploma blank.
     */
    public function available(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => DiplomaBlank::STATUS_IN_STOCK,
            'issue_date' => null,
            'recall_date' => null,
            'issue_reason' => null,
            'recall_reason' => null,
        ]);
    }

    /**
     * Create an issued diploma blank.
     */
    public function issued(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => DiplomaBlank::STATUS_ISSUED,
            'issue_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'issue_reason' => 'Cấp văn bằng cho sinh viên tốt nghiệp',
        ]);
    }

    /**
     * Create a damaged diploma blank.
     */
    public function damaged(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => DiplomaBlank::STATUS_DAMAGED,
            'recall_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'recall_reason' => $this->faker->randomElement([
                'Phôi bị rách',
                'Phôi bị ố vàng',
                'Lỗi in ấn',
                'Phôi bị thủng',
            ]),
        ]);
    }

    /**
     * Create with specific type.
     */
    public function withType(DiplomaBlankType $type): static
    {
        return $this->state(fn(array $attributes) => [
            'type_id' => $type->type_id,
        ]);
    }
}