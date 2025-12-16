<?php

namespace Database\Factories;

use App\Models\Degree;
use App\Models\Student;
use App\Models\DiplomaBlank;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Degree>
 */
class DegreeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $graduationYear = $this->faker->numberBetween(2020, date('Y'));

        return [
            'student_id' => Student::factory(),
            'diploma_blank_id' => DiplomaBlank::factory()->available(),
            'degree_type' => $this->faker->randomElement(['bachelor', 'master', 'doctor']),
            'registration_number' => $this->faker->unique()->regexify('[0-9]{4}/[A-Z]{2}-[0-9]{4}'),
            'granting_date' => $this->faker->dateTimeBetween($graduationYear . '-06-01', $graduationYear . '-12-31')->format('Y-m-d'),
            'graduation_year' => $graduationYear,
            'defense_date' => null, // Will be set for master/doctor degrees
            'ranking' => $this->faker->randomElement(['Xuất sắc', 'Giỏi', 'Khá', 'Trung bình']),
            'decision_number' => $this->faker->regexify('[0-9]{3}/QĐ-[A-Z]{2}'),
        ];
    }

    /**
     * Create degree with excellent ranking.
     */
    public function excellent(): static
    {
        return $this->state(fn(array $attributes) => [
            'ranking' => 'Xuất sắc',
        ]);
    }

    /**
     * Create degree with good ranking.
     */
    public function good(): static
    {
        return $this->state(fn(array $attributes) => [
            'ranking' => 'Giỏi',
        ]);
    }

    /**
     * Create degree for specific student.
     */
    public function forStudent(Student $student): static
    {
        return $this->state(fn(array $attributes) => [
            'student_id' => $student->student_id,
        ]);
    }

    /**
     * Create degree with specific diploma blank.
     */
    public function withDiplomaBlank(DiplomaBlank $diplomaBlank): static
    {
        return $this->state(fn(array $attributes) => [
            'diploma_blank_id' => $diplomaBlank->diploma_blank_id,
        ]);
    }

    /**
     * Create degree for specific graduation year.
     */
    public function forYear(int $year): static
    {
        return $this->state(fn(array $attributes) => [
            'graduation_year' => $year,
            'granting_date' => $this->faker->dateTimeBetween($year . '-06-01', $year . '-12-31')->format('Y-m-d'),
        ]);
    }
}