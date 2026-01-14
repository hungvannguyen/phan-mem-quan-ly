<?php

namespace Database\Seeders;

use App\Models\DamageReason;
use App\Models\Degree;
use App\Models\DiplomaBlank;
use App\Models\DiplomaBlankType;
use App\Models\Major;
use App\Models\Role;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Models\User;
use App\Enums\DegreeStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestSeeder extends Seeder
{
    /**
     * Test seeder logic without saving to database
     */
    public function run(): void
    {
        // Use transaction to rollback everything
        DB::transaction(function () {
            $this->command->info('🧪 Starting test seeder (will be rolled back)...');

            // Create test majors
            $majorData = [
                ['major_name' => 'Test IT', 'major_code' => 'TEST_IT01'],
                ['major_name' => 'Test Business', 'major_code' => 'TEST_BUS01'],
            ];

            $majors = collect();
            foreach ($majorData as $data) {
                $major = Major::create($data);
                $majors->push($major);
                $this->command->info("✓ Created major: {$major->major_name}");
            }

            // Create students with different statuses
            $allStudents = collect();

            // Create graduated students (10 students for test)
            $graduatedStudents = Student::factory(10)->create()->each(function ($student) use ($majors) {
                $student->update([
                    'major_id' => $majors->random()->major_id,
                    'status' => \App\Enums\StudentStatus::Graduate, // Đã tốt nghiệp
                ]);
            });
            $allStudents = $allStudents->merge($graduatedStudents);
            $this->command->info("✓ Created {$graduatedStudents->count()} graduated students");

            // Create studying students (5 students for test)
            $studyingStudents = Student::factory(5)->create()->each(function ($student) use ($majors) {
                $student->update([
                    'major_id' => $majors->random()->major_id,
                    'status' => \App\Enums\StudentStatus::Studying, // Đang học
                ]);
            });
            $allStudents = $allStudents->merge($studyingStudents);
            $this->command->info("✓ Created {$studyingStudents->count()} studying students");

            // Create dropout students (2 students for test)
            $dropoutStudents = Student::factory(2)->create()->each(function ($student) use ($majors) {
                $student->update([
                    'major_id' => $majors->random()->major_id,
                    'status' => \App\Enums\StudentStatus::DropOut, // Bỏ học
                ]);
            });
            $allStudents = $allStudents->merge($dropoutStudents);
            $this->command->info("✓ Created {$dropoutStudents->count()} dropout students");

            // Create diploma blank types for test
            $universityType = DiplomaBlankType::create([
                'type_name' => 'Test Bằng Đại học',
                'prefix' => 'TEST_DH',
            ]);
            $this->command->info("✓ Created diploma blank type: {$universityType->type_name}");

            // Create issued diploma blanks (10 for test)
            $issuedBlanks = DiplomaBlank::factory()
                ->count(10)
                ->issued()
                ->create()
                ->each(function ($blank) use ($universityType) {
                    $blank->update([
                        'type_id' => $universityType->type_id
                    ]);
                });
            $this->command->info("✓ Created {$issuedBlanks->count()} issued diploma blanks");

            // Create degrees only for our test graduated students using issued diploma blanks
            $testGraduatedStudents = $graduatedStudents; // Use our test graduated students
            $degreesCreated = 0;

            $issuedBlanks->each(function ($blank, $index) use ($testGraduatedStudents, &$degreesCreated) {
                if ($index < $testGraduatedStudents->count()) {
                    Degree::factory()->create([
                        'student_id' => $testGraduatedStudents[$index]->student_id,
                        'diploma_blank_id' => $blank->diploma_blank_id,
                        'status' => DegreeStatus::ISSUED, // Đã cấp
                    ]);

                    // Update blank status to issued
                    $blank->update(['status' => DiplomaBlank::STATUS_ISSUED]);
                    $degreesCreated++;
                }
            });

            // Verify logic
            $this->command->info('');
            $this->command->info('🔍 Verification Results:');
            $this->command->info("- Total students created: {$allStudents->count()}");
            $this->command->info("- Graduated students: {$graduatedStudents->count()}");
            $this->command->info("- Studying students: {$studyingStudents->count()}");
            $this->command->info("- Dropout students: {$dropoutStudents->count()}");
            $this->command->info("- Degrees created: {$degreesCreated}");
            $this->command->info("- Available blanks: {$issuedBlanks->count()}");

            // Verify only graduated students have degrees (from our test data only)
            $testStudentIds = $allStudents->pluck('student_id');
            $testStudentsWithDegrees = Student::whereHas('degrees')->whereIn('student_id', $testStudentIds)->get();
            $graduatedStudentsWithDegrees = $testStudentsWithDegrees->where('status', \App\Enums\StudentStatus::Graduate);
            $nonGraduatedStudentsWithDegrees = $testStudentsWithDegrees->where('status', '!=', \App\Enums\StudentStatus::Graduate);

            $this->command->info('');
            $this->command->info('✅ Logic Validation (Test Data Only):');
            $this->command->info("- Test students with degrees: {$testStudentsWithDegrees->count()}");
            $this->command->info("- Graduated students with degrees: {$graduatedStudentsWithDegrees->count()}");
            $this->command->info("- Non-graduated students with degrees: {$nonGraduatedStudentsWithDegrees->count()}");

            if ($nonGraduatedStudentsWithDegrees->count() === 0) {
                $this->command->info("✅ PASS: Only graduated students have degrees!");
            } else {
                $this->command->error("❌ FAIL: Non-graduated students have degrees!");
            }

            if ($graduatedStudentsWithDegrees->count() === min($graduatedStudents->count(), $issuedBlanks->count())) {
                $this->command->info("✅ PASS: Correct number of degrees issued!");
            } else {
                $this->command->error("❌ FAIL: Incorrect number of degrees issued!");
            }

            // Show sample data
            $this->command->info('');
            $this->command->info('📋 Sample Students:');
            $allStudents->take(5)->each(function ($student) {
                $status = $student->status->label();
                $hasDegree = $student->degrees()->exists() ? '✓ Has Degree' : '✗ No Degree';
                $this->command->info("  - {$student->full_name} ({$status}) - {$hasDegree}");
            });

            $this->command->info('');
            $this->command->info('🔄 Rolling back transaction (no data saved)...');

            // Throw exception to rollback transaction
            throw new \Exception('Test completed - rolling back');
        });
    }
}
