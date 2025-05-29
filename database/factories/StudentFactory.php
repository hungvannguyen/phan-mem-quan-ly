<?php

namespace Database\Factories;

use App\Enums\StudentGender;
use App\Enums\StudentStatus;
use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class StudentFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
				'name' => $this->faker->name(),
				'date_of_birth' => $this->faker->date(),
				'place_of_birth' => $this->faker->city(),
				'gender' => $this->faker->randomElement(StudentGender::class),
				'nation' => $this->faker->country(),
				'nationality' => $this->faker->country(),
				'training_id' => $this->faker->randomElement([1, 2, 3]),
				'number_in_the_book' => $this->faker->unique()->numberBetween(1000, 9999),
				'status' => $this->faker->randomElement(StudentStatus::class),
				'created_at' => now(),
		];
	}
}
