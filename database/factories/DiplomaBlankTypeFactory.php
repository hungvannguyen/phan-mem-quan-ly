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
        $types = [
            ['name' => 'Bằng tốt nghiệp Đại học', 'prefix' => 'DH'],
            ['name' => 'Bằng tốt nghiệp Cao đẳng', 'prefix' => 'CD'],
            ['name' => 'Chứng chỉ Tin học', 'prefix' => 'TH'],
            ['name' => 'Chứng chỉ Ngoại ngữ', 'prefix' => 'NN'],
            ['name' => 'Bằng Thạc sĩ', 'prefix' => 'THS'],
            ['name' => 'Chứng chỉ nghề', 'prefix' => 'CN'],
        ];

        $type = $this->faker->randomElement($types);

        return [
            'type_name' => $type['name'],
            'prefix' => $type['prefix'],
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
