<?php

namespace Database\Factories;

use App\Models\DiplomaBatche;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiplomaBatche>
 */
class DiplomaBatcheFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
				'import_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
				'quality' => $this->faker->numberBetween(0, 1000),
				'remaining' => $this->faker->numberBetween(0, 100),
				'error' => $this->faker->numberBetween(0, 10),
				'description' => $this->faker->sentence(),
		];
	}
}
