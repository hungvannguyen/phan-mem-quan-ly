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

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create roles using factory
        $adminRole = Role::factory()->admin()->create();
        $diplomaManagerRole = Role::factory()->diplomaManager()->create();
        $certificateManagerRole = Role::factory()->certificateManager()->create();
        $studentManagerRole = Role::factory()->studentManager()->create();
        $viewerRole = Role::factory()->viewer()->create();

        // 2. Create users using factory
        $adminUser = User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'full_name' => 'Quản trị viên',
            'email' => 'admin@example.com',
            'is_active' => true,
        ]);
        $adminUser->roles()->attach($adminRole->role_id);

        $diplomaUser = User::factory()->create([
            'username' => 'diploma_manager',
            'password' => bcrypt('password'),
            'full_name' => 'Người quản lý văn bằng',
            'email' => 'diploma@example.com',
            'is_active' => true,
        ]);
        $diplomaUser->roles()->attach($diplomaManagerRole->role_id);

        // Create additional users
        User::factory(8)->create()->each(function ($user) use ($diplomaManagerRole) {
            $user->roles()->attach($diplomaManagerRole->role_id);
        });

        // 3. Create majors using factory
        $majorData = [
            ['Công nghệ thông tin', 'IT01'],
            ['Kế toán', 'ACC01'],
            ['Quản trị kinh doanh', 'BUS01'],
            ['Ngôn ngữ Anh', 'ENG01'],
            ['Thiết kế đồ họa', 'GD01'],
            ['Marketing', 'MKT01'],
            ['Tài chính - Ngân hàng', 'FIN01'],
            ['Luật kinh doanh', 'LAW01'],
            ['Điều dưỡng', 'NUR01'],
            ['Y học cổ truyền', 'TCM01'],
        ];

        $majors = collect();
        foreach ($majorData as [$name, $code]) {
            $major = Major::factory()->withName($name, $code)->create();
            $majors->push($major);
        }

        // 4. Create diploma blank types using factory
        // a) Các loại văn bằng
        $bachelorType = DiplomaBlankType::factory()->bachelor()->create();
        $engineerType = DiplomaBlankType::factory()->engineer()->create();
        $masterType = DiplomaBlankType::factory()->master()->create();
        $doctorType = DiplomaBlankType::factory()->doctor()->create();
        $intermediatePoliticalType = DiplomaBlankType::factory()->intermediatePolitical()->create();
        $advancedPoliticalType = DiplomaBlankType::factory()->advancedPolitical()->create();

        // b) Các loại chứng chỉ
        $sixMonthCertType = DiplomaBlankType::factory()->sixMonthCertificate()->create();
        $equivalentIntermediatePoliticalType = DiplomaBlankType::factory()->equivalentIntermediatePolitical()->create();
        $militaryCertType = DiplomaBlankType::factory()->militaryCertificate()->create();
        $knowledgeSupplementCertType = DiplomaBlankType::factory()->knowledgeSupplementCertificate()->create();
        $otherTrainingCertType = DiplomaBlankType::factory()->otherTrainingCertificate()->create();

        $allTypes = collect([
            $bachelorType,
            $engineerType,
            $masterType,
            $doctorType,
            $intermediatePoliticalType,
            $advancedPoliticalType,
            $sixMonthCertType,
            $equivalentIntermediatePoliticalType,
            $militaryCertType,
            $knowledgeSupplementCertType,
            $otherTrainingCertType
        ]);

        // 5. Create students with different statuses using factory
        $allStudents = collect();

        // Graduated students (30)
        $graduatedStudents = Student::factory(30)->create()->each(function ($student) use ($majors) {
            $student->update([
                'major_id' => $majors->random()->major_id,
                'status' => \App\Enums\StudentStatus::Graduate,
            ]);
        });
        $allStudents = $allStudents->merge($graduatedStudents);

        // Studying students (15)
        $studyingStudents = Student::factory(15)->create()->each(function ($student) use ($majors) {
            $student->update([
                'major_id' => $majors->random()->major_id,
                'status' => \App\Enums\StudentStatus::Studying,
            ]);
        });
        $allStudents = $allStudents->merge($studyingStudents);

        // Dropout students (5)
        $dropoutStudents = Student::factory(5)->create()->each(function ($student) use ($majors) {
            $student->update([
                'major_id' => $majors->random()->major_id,
                'status' => \App\Enums\StudentStatus::DropOut,
            ]);
        });
        $allStudents = $allStudents->merge($dropoutStudents);

        // 6. Create diploma blanks using factory
        // Available diploma blanks (100)
        $availableBlanks = DiplomaBlank::factory()
            ->count(100)
            ->available()
            ->create()
            ->each(function ($blank) use ($allTypes) {
                $blank->update([
                    'type_id' => $allTypes->random()->type_id
                ]);
            });

        // Issued diploma blanks (30)
        $issuedBlanks = DiplomaBlank::factory()
            ->count(30)
            ->issued()
            ->create()
            ->each(function ($blank) use ($allTypes) {
                $blank->update([
                    'type_id' => $allTypes->random()->type_id
                ]);
            });

        // Damaged diploma blanks (10)
        DiplomaBlank::factory()
            ->count(10)
            ->damaged()
            ->create()
            ->each(function ($blank) use ($allTypes) {
                $blank->update([
                    'type_id' => $allTypes->random()->type_id
                ]);
            });

        // 7. Create degrees for graduated students using factory
        $graduatedStudentsOnly = Student::where('status', \App\Enums\StudentStatus::Graduate)->get();
        $issuedBlanks->each(function ($blank, $index) use ($graduatedStudentsOnly) {
            if ($index < $graduatedStudentsOnly->count()) {
                Degree::factory()->create([
                    'student_id' => $graduatedStudentsOnly[$index]->student_id,
                    'diploma_blank_id' => $blank->diploma_blank_id,
                ]);

                $blank->update(['status' => DiplomaBlank::STATUS_ISSUED]);
            }
        });

        // 8. Create system settings using factory
        SystemSetting::factory()->schoolName('Trường Đại học ABC')->create();
        SystemSetting::factory()->address('123 Đường ABC, Quận XYZ, TP. Hồ Chí Minh')->create();
        SystemSetting::factory()->phone('(028) 1234 5678')->create();
        SystemSetting::factory()->email('contact@abc.edu.vn')->create();
        SystemSetting::create([
            'setting_key' => 'Website',
            'setting_value' => 'https://abc.edu.vn',
        ]);

        // 9. Create damage reasons using factory
        $damageReasons = [
            ['Rách phôi', 'Phôi bị rách trong quá trình sử dụng'],
            ['Ố vàng', 'Phôi bị ố vàng do bảo quản không tốt'],
            ['Lỗi in ấn', 'Phôi có lỗi trong quá trình in ấn'],
            ['Thủng lỗ', 'Phôi bị thủng lỗ'],
        ];

        foreach ($damageReasons as [$name, $description]) {
            DamageReason::factory()->withReason($name, $description)->create();
        }

        // Summary information
        $this->command->info('Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('=== User Accounts ===');
        $this->command->info('Admin: admin / password');
        $this->command->info('Diploma Manager: diploma_manager / password');
        $this->command->info('');
        $this->command->info('=== Students ===');
        $this->command->info('- Graduated: ' . $graduatedStudents->count());
        $this->command->info('- Studying: ' . $studyingStudents->count());
        $this->command->info('- Dropout: ' . $dropoutStudents->count());
        $this->command->info('- Total: ' . $allStudents->count());
        $this->command->info('');
        $this->command->info('=== Diploma Types ===');
        $this->command->info('Văn bằng: 6 loại (Cử nhân, Kỹ sư, Thạc sĩ, Tiến sĩ, TC LLCT, CC LLCT)');
        $this->command->info('Chứng chỉ: 5 loại (NV 6 tháng, TĐ TC LLCT, QSVT 45 ngày, BSKT, Bồi dưỡng khác)');
        $this->command->info('');
        $this->command->info('=== Diploma Blanks ===');
        $this->command->info('- Available: 100');
        $this->command->info('- Issued: 30');
        $this->command->info('- Damaged: 10');
        $this->command->info('- Total: 140');
        $this->command->info('');
        $this->command->info('=== Degrees Issued ===');
        $this->command->info('Total degrees: ' . $graduatedStudentsOnly->count());
    }
}
