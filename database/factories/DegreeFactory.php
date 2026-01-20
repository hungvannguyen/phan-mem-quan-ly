<?php

namespace Database\Factories;

use App\Enums\DegreeStatus;
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
        $grantingDate = $this->faker->dateTimeBetween($graduationYear . '-06-01', $graduationYear . '-12-31');

        // Generate training dates (typical 3-5 years of training before granting date)
        $trainingYears = $this->faker->numberBetween(3, 5);
        $trainingStartDate = (clone $grantingDate)->modify("-{$trainingYears} years");
        $trainingEndDate = (clone $grantingDate)->modify('-2 months');

        // Generate council decision date (for master/doctor degrees, typically 2-4 months before granting)
        $councilDecisionDate = (clone $grantingDate)->modify('-' . $this->faker->numberBetween(2, 4) . ' months');

        // Generate graduation decision date (typically 1-2 months before granting)
        $graduationDecisionDate = (clone $grantingDate)->modify('-' . $this->faker->numberBetween(1, 2) . ' months');

        $majorNames = [
            'Công nghệ thông tin',
            'Kế toán',
            'Quản trị kinh doanh',
            'Luật kinh tế',
            'Ngôn ngữ Anh',
            'Du lịch',
            'Tài chính - Ngân hàng',
            'Marketing',
            'Quản lý đất đai',
            'Xây dựng dân dụng',
        ];

        $trainingTypes = [
            'Chính quy',
            'Chính quy',
            'Chính quy', // Tăng tỷ lệ chính quy
            'Liên thông',
            'Vừa làm vừa học',
            'Từ xa',
        ];

        return [
            'student_id' => Student::factory(),
            'diploma_blank_id' => DiplomaBlank::factory()->available(),
            'degree_type' => $this->faker->randomElement(['bachelor', 'master', 'doctor']),
            'registration_number' => $this->faker->unique()->regexify('[0-9]{4}/[A-Z]{2}-[0-9]{4}'),
            'number_in_the_book' => 'VB-' . $this->faker->unique()->numberBetween(100000, 999999),
            'major_name' => $this->faker->randomElement($majorNames),
            'granting_date' => $grantingDate->format('Y-m-d'),
            'graduation_year' => $graduationYear,
            'defense_date' => null, // Will be set for master/doctor degrees
            'training_start_date' => $trainingStartDate->format('Y-m-d'),
            'training_end_date' => $trainingEndDate->format('Y-m-d'),
            'training_type' => $this->faker->randomElement($trainingTypes),
            'ranking' => $this->faker->randomElement(['Xuất sắc', 'Giỏi', 'Khá', 'Trung bình']),
            'council_decision_number' => $this->faker->regexify('[0-9]{3}/QĐ-HĐ-[0-9]{4}'),
            'council_decision_date' => $councilDecisionDate->format('Y-m-d'),
            'graduation_decision_number' => $this->faker->regexify('[0-9]{3}/QĐ-TN-[0-9]{4}'),
            'graduation_decision_date' => $graduationDecisionDate->format('Y-m-d'),
            'status' => $this->faker->randomElement([DegreeStatus::NOT_ISSUED, DegreeStatus::ISSUED, DegreeStatus::RECALLED]),
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
