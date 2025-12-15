<?php

namespace Database\Factories;

use App\Enums\StudentGender;
use App\Enums\StudentStatus;
use App\Models\Student;
use App\Models\Major;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
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
            'student_code' => $this->faker->unique()->regexify('[A-Z]{2}[0-9]{6}'),
            'full_name' => $this->faker->name(),
            'date_of_birth' => $this->faker->dateTimeBetween('-30 years', '-18 years')->format('Y-m-d'),
            'class_name' => $this->faker->regexify('[A-Z]{2}[0-9]{2}[A-Z]{1}'),
            'course' => 'K' . $this->faker->numberBetween(45, 55),
            'academic_year' => $this->faker->numberBetween(2015, 2024) . '-' . $this->faker->numberBetween(2019, 2028),
            'major_id' => 1, // Default major_id, will be overridden in seeder
            'place_of_birth' => $this->faker->city(),
            'gender' => $this->faker->randomElement(StudentGender::cases())->value,
            'nation' => 'Kinh',
            'nationality' => 'Việt Nam',
            'number_in_the_book' => $this->faker->unique()->numberBetween(1000, 9999),
            'status' => $this->faker->randomElement(StudentStatus::cases())->value,
        ];
    }

    /**
     * Create a student with a specific major.
     */
    public function withMajor(Major $major): static
    {
        return $this->state(fn(array $attributes) => [
            'major_id' => $major->major_id,
        ]);
    }

    /**
     * Create a male student.
     */
    public function male(): static
    {
        return $this->state(fn(array $attributes) => [
            'gender' => StudentGender::Male->value,
        ]);
    }

    /**
     * Create a female student.
     */
    public function female(): static
    {
        return $this->state(fn(array $attributes) => [
            'gender' => StudentGender::Female->value,
        ]);
    }

    /**
     * Create a studying student.
     */
    public function studying(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => StudentStatus::Studying->value,
        ]);
    }

    /**
     * Create a graduated student.
     */
    public function graduated(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => StudentStatus::Graduate->value,
        ]);
    }

    /**
     * Create a graduated student with a degree.
     */
    public function withDegree(): static
    {
        return $this->graduated()->afterCreating(function ($student) {
            // Create a degree for this student
            \App\Models\Degree::factory()->create([
                'student_id' => $student->student_id,
            ]);
        });
    }

    /**
     * Create a dropped out student.
     */
    public function droppedOut(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => StudentStatus::DropOut->value,
        ]);
    }
}
