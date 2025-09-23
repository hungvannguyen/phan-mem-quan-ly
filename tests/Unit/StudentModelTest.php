<?php

namespace Tests\Unit;

use App\Enums\StudentGender;
use App\Enums\StudentStatus;
use App\Models\Major;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test major
        Major::factory()->create([
            'major_id' => 1,
            'major_code' => 'CNTT',
            'major_name' => 'Công nghệ thông tin'
        ]);
    }

    public function test_can_create_student_with_all_fields(): void
    {
        $student = Student::factory()->create([
            'student_code' => 'SV123456',
            'full_name' => 'Nguyễn Văn An',
            'date_of_birth' => '2000-01-01',
            'class_name' => 'CNTT01A',
            'major_id' => 1,
            'place_of_birth' => 'Hà Nội',
            'gender' => StudentGender::Male->value,
            'nation' => 'Kinh',
            'nationality' => 'Việt Nam',
            'number_in_the_book' => '1234',
            'status' => StudentStatus::Studying->value,
        ]);

        $this->assertInstanceOf(Student::class, $student);
        $this->assertEquals('SV123456', $student->student_code);
        $this->assertEquals('Nguyễn Văn An', $student->full_name);
        $this->assertEquals(StudentGender::Male, $student->gender);
        $this->assertEquals(StudentStatus::Studying, $student->status);
    }

    public function test_student_gender_and_status_enums_work(): void
    {
        $student = Student::factory()->male()->studying()->create();

        $this->assertEquals(StudentGender::Male, $student->gender);
        $this->assertEquals(StudentStatus::Studying, $student->status);
        $this->assertEquals('Nam', $student->gender_label);
        $this->assertEquals('Đang học', $student->status_label);
    }

    public function test_student_helper_methods(): void
    {
        $student = Student::factory()->create([
            'gender' => StudentGender::Female->value,
            'status' => StudentStatus::Graduate->value,
        ]);

        $this->assertTrue($student->hasGraduated());
        $this->assertFalse($student->isStudying());
        $this->assertStringContainsString('Chị', $student->full_name_with_gender);
    }

    public function test_student_age_calculation(): void
    {
        $student = Student::factory()->create([
            'date_of_birth' => '2000-01-01'
        ]);

        $expectedAge = (int) \Carbon\Carbon::parse('2000-01-01')->diffInYears(now());
        $this->assertEquals($expectedAge, $student->age);
    }

    public function test_student_belongs_to_major(): void
    {
        $student = Student::factory()->create(['major_id' => 1]);

        $this->assertInstanceOf(Major::class, $student->major);
        $this->assertEquals(1, $student->major->major_id);
    }

    public function test_factory_states_work(): void
    {
        $maleStudent = Student::factory()->male()->create();
        $femaleStudent = Student::factory()->female()->create();
        $studyingStudent = Student::factory()->studying()->create();
        $droppedOutStudent = Student::factory()->droppedOut()->create();

        $this->assertEquals(StudentGender::Male, $maleStudent->gender);
        $this->assertEquals(StudentGender::Female, $femaleStudent->gender);
        $this->assertEquals(StudentStatus::Studying, $studyingStudent->status);
        $this->assertEquals(StudentStatus::DropOut, $droppedOutStudent->status);
    }
}
