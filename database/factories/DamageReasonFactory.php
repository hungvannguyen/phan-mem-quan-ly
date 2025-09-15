<?php

namespace Database\Factories;

use App\Models\DamageReason;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DamageReason>
 */
class DamageReasonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reasons = [
            ['name' => 'Rách phôi', 'description' => 'Phôi bị rách trong quá trình sử dụng'],
            ['name' => 'Ố vàng', 'description' => 'Phôi bị ố vàng do bảo quản không tốt'],
            ['name' => 'Lỗi in ấn', 'description' => 'Phôi có lỗi trong quá trình in ấn'],
            ['name' => 'Thủng lỗ', 'description' => 'Phôi bị thủng lỗ'],
            ['name' => 'Bị nước', 'description' => 'Phôi bị ướt nước gây hư hại'],
            ['name' => 'Bị cháy', 'description' => 'Phôi bị cháy trong sự cố'],
        ];

        $reason = $this->faker->randomElement($reasons);

        return [
            'name' => $reason['name'],
            'description' => $reason['description'],
        ];
    }

    /**
     * Create specific damage reason.
     */
    public function withReason(string $name, string $description): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => $name,
            'description' => $description,
        ]);
    }
}