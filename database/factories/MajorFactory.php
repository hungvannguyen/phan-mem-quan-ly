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
        $majors = [
            ['name' => 'Công nghệ thông tin', 'code' => 'IT'],
            ['name' => 'Kế toán', 'code' => 'ACC'],
            ['name' => 'Quản trị kinh doanh', 'code' => 'BUS'],
            ['name' => 'Ngôn ngữ Anh', 'code' => 'ENG'],
            ['name' => 'Thiết kế đồ họa', 'code' => 'GD'],
            ['name' => 'Marketing', 'code' => 'MKT'],
            ['name' => 'Tài chính - Ngân hàng', 'code' => 'FIN'],
            ['name' => 'Luật kinh doanh', 'code' => 'LAW'],
        ];

        $major = $this->faker->randomElement($majors);

        return [
            'major_name' => $major['name'],
            'major_code' => $major['code'] . $this->faker->numberBetween(10, 99),

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
