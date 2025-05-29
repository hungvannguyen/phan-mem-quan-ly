<?php

namespace Database\Seeders;

use App\Models\DamageReason;
use App\Models\Student;
use App\Models\Training;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	/**
	 * Seed the application's database.
	 */
	public function run(): void
	{
		// User::factory(10)->create();

		User::factory()->create([
				'name' => 'Test User',
				'email' => 'test@example.com',
				'password' => bcrypt('password'),
				'is_admin' => 1,
		]);

		$training = Training::factory(10)->create();

		Student::factory(50)->create(
				[
						'training_id' => $training->random()->id,
				]
		);

		DamageReason::factory(4)->create();

	}
}
