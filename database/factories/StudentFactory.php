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
        $lastNames = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương'];
        $middleNames = ['Văn', 'Thị', 'Minh', 'Hữu', 'Đức', 'Thanh', 'Thu', 'Anh', 'Quốc', 'Công', 'Gia', 'Khánh', 'Bảo', 'Hồng'];
        $maleFirstNames = ['Nam', 'Hùng', 'Dũng', 'Cường', 'Tuấn', 'Đạt', 'Khoa', 'Phong', 'Hải', 'Long', 'An', 'Quân', 'Kiên', 'Bình', 'Tùng', 'Hoàng', 'Thắng', 'Thành', 'Tài', 'Trung'];
        $femaleFirstNames = ['Linh', 'Hương', 'Hà', 'Mai', 'Lan', 'Trang', 'Nga', 'Phương', 'Chi', 'My', 'Thảo', 'Vy', 'Nhung', 'Ngọc', 'Hoa', 'Yến', 'Dung', 'Huệ', 'Quyên', 'Tâm'];

        $gender = $this->faker->randomElement(StudentGender::cases());
        $firstName = $gender === StudentGender::Male
            ? $this->faker->randomElement($maleFirstNames)
            : $this->faker->randomElement($femaleFirstNames);

        $fullName = $this->faker->randomElement($lastNames) . ' ' .
            $this->faker->randomElement($middleNames) . ' ' .
            $firstName;

        // Nhiều khóa hơn: từ 2010 đến 2023 (14 khóa)
        $yearStart = $this->faker->numberBetween(2010, 2023);
        $yearEnd = $yearStart + 4;

        // Nhiều hình thức đào tạo hơn (must match enum in migration)
        $trainingTypes = [
            'Chính quy',
            'Chính quy',
            'Chính quy', // Tăng tỷ lệ chính quy
            'Liên thông',
            'Vừa làm vừa học',
            'Từ xa',
        ];

        $vietnamProvinces = [
            'Hà Nội',
            'Hồ Chí Minh',
            'Đà Nẵng',
            'Hải Phòng',
            'Cần Thơ',
            'Nghệ An',
            'Thanh Hóa',
            'Nam Định',
            'Hải Dương',
            'Bắc Ninh',
            'Thái Bình',
            'Quảng Ninh',
            'Ninh Bình',
            'Hà Nam',
            'Vĩnh Phúc',
            'Bắc Giang',
            'Phú Thọ',
            'Thái Nguyên',
            'Hưng Yên',
            'Hà Tĩnh',
            'Quảng Bình',
            'Quảng Trị',
            'Thừa Thiên Huế',
            'Quảng Nam',
            'Quảng Ngãi',
            'Bình Định',
            'Phú Yên',
            'Khánh Hòa',
            'Ninh Thuận',
            'Bình Thuận',
            'Kon Tum',
            'Gia Lai',
            'Đắk Lắk',
            'Đắk Nông',
            'Lâm Đồng',
            'Bình Phước',
            'Tây Ninh',
            'Bình Dương',
            'Đồng Nai',
            'Bà Rịa - Vũng Tàu',
            'Long An',
            'Tiền Giang',
            'Bến Tre',
            'Trà Vinh',
            'Vĩnh Long',
            'Đồng Tháp',
            'An Giang',
            'Kiên Giang',
            'Cà Mau',
            'Hậu Giang',
            'Sóc Trăng',
            'Bạc Liêu'
        ];

        return [
            'student_code' => $this->faker->unique()->regexify('SV[0-9]{6}'),
            'full_name' => $fullName,
            'date_of_birth' => $this->faker->dateTimeBetween('-35 years', '-18 years')->format('Y-m-d'),
            'class_name' => 'K' . substr($yearStart, 2) . '-' . $this->faker->regexify('[A-Z]{3}'),
            'course' => 'K' . substr($yearStart, 2),
            'academic_year' => $yearStart . ' - ' . $yearEnd,
            'major_id' => 1, // Default major_id, will be overridden in seeder
            'place_of_birth' => $this->faker->randomElement($vietnamProvinces),
            'hometown' => $this->faker->randomElement($vietnamProvinces),
            'place_of_origin' => $this->faker->randomElement($vietnamProvinces),
            'gender' => $gender->value,
            'nation' => $this->faker->randomElement(['Kinh', 'Tày', 'Thái', 'Mường', 'Khmer', 'Hoa', 'Nùng', 'H\'Mông']),
            'nationality' => 'Việt Nam',
            'training_type' => $this->faker->randomElement($trainingTypes),
            'number_in_the_book' => 'VB-' . $this->faker->unique()->numberBetween(100000, 999999),
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
