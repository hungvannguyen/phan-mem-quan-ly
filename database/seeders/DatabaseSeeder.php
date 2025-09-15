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
        // Create roles first
        $adminRole = Role::factory()->admin()->create();
        $diplomaManagerRole = Role::factory()->diplomaManager()->create();
        Role::factory()->create([
            'role_name' => 'CertificateManager',
            'description' => 'Quản lý chứng chỉ',
        ]);
        Role::factory()->create([
            'role_name' => 'StudentManager',
            'description' => 'Quản lý sinh viên',
        ]);
        Role::factory()->create([
            'role_name' => 'Viewer',
            'description' => 'Chỉ xem thông tin',
        ]);

        // Create admin user
        $adminUser = User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'full_name' => 'Quản trị viên',
            'email' => 'admin@example.com',
            'is_active' => true,
        ]);
        $adminUser->roles()->attach($adminRole->role_id);

        // Create diploma manager user
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
            // Assign random roles to users
            $user->roles()->attach($diplomaManagerRole->role_id);
        });

        // Create majors (replacing trainings)
        $majorData = [
            ['major_name' => 'Công nghệ thông tin', 'major_code' => 'IT01'],
            ['major_name' => 'Kế toán', 'major_code' => 'ACC01'],
            ['major_name' => 'Quản trị kinh doanh', 'major_code' => 'BUS01'],
            ['major_name' => 'Ngôn ngữ Anh', 'major_code' => 'ENG01'],
            ['major_name' => 'Thiết kế đồ họa', 'major_code' => 'GD01'],
            ['major_name' => 'Marketing', 'major_code' => 'MKT01'],
            ['major_name' => 'Tài chính - Ngân hàng', 'major_code' => 'FIN01'],
            ['major_name' => 'Luật kinh doanh', 'major_code' => 'LAW01'],
            ['major_name' => 'Điều dưỡng', 'major_code' => 'NUR01'],
            ['major_name' => 'Y học cổ truyền', 'major_code' => 'TCM01'],
        ];

        $majors = collect();
        foreach ($majorData as $data) {
            $major = Major::create($data);
            $majors->push($major);
        }

        // Create students
        Student::factory(50)->create()->each(function ($student) use ($majors) {
            $student->update(['major_id' => $majors->random()->major_id]);
        });

        // Create diploma blank types directly to avoid duplicates
        $universityType = DiplomaBlankType::create([
            'type_name' => 'Bằng tốt nghiệp Đại học',
            'prefix' => 'DH',
        ]);
        $collegeType = DiplomaBlankType::create([
            'type_name' => 'Bằng tốt nghiệp Cao đẳng',
            'prefix' => 'CD',
        ]);
        $itCertType = DiplomaBlankType::create([
            'type_name' => 'Chứng chỉ Tin học',
            'prefix' => 'TH',
        ]);
        $langCertType = DiplomaBlankType::create([
            'type_name' => 'Chứng chỉ Ngoại ngữ',
            'prefix' => 'NN',
        ]);

        // Create diploma blanks
        $diplomaBlanks = collect();

        // Create available diploma blanks
        $availableBlanks = DiplomaBlank::factory()
            ->count(100)
            ->available()
            ->create()
            ->each(function ($blank) use ($universityType, $collegeType, $itCertType, $langCertType) {
                $blank->update([
                    'type_id' => collect([$universityType->type_id, $collegeType->type_id, $itCertType->type_id, $langCertType->type_id])->random()
                ]);
            });
        $diplomaBlanks = $diplomaBlanks->merge($availableBlanks);

        // Create issued diploma blanks
        $issuedBlanks = DiplomaBlank::factory()
            ->count(30)
            ->issued()
            ->create()
            ->each(function ($blank) use ($universityType, $collegeType, $itCertType, $langCertType) {
                $blank->update([
                    'type_id' => collect([$universityType->type_id, $collegeType->type_id, $itCertType->type_id, $langCertType->type_id])->random()
                ]);
            });
        $diplomaBlanks = $diplomaBlanks->merge($issuedBlanks);

        // Create damaged diploma blanks
        DiplomaBlank::factory()
            ->count(10)
            ->damaged()
            ->create()
            ->each(function ($blank) use ($universityType, $collegeType, $itCertType, $langCertType) {
                $blank->update([
                    'type_id' => collect([$universityType->type_id, $collegeType->type_id, $itCertType->type_id, $langCertType->type_id])->random()
                ]);
            });

        // Create degrees using issued diploma blanks
        $students = Student::all();
        $issuedBlanks->each(function ($blank, $index) use ($students) {
            if ($index < $students->count()) {
                Degree::factory()->create([
                    'student_id' => $students[$index]->student_id,
                    'diploma_blank_id' => $blank->diploma_blank_id,
                ]);

                // Update blank status to issued
                $blank->update(['status' => DiplomaBlank::STATUS_ISSUED]);
            }
        });

        // Create system settings
        SystemSetting::factory()->schoolName('Trường Đại học ABC')->create();
        SystemSetting::factory()->address('123 Đường ABC, Quận XYZ, TP. Hồ Chí Minh')->create();
        SystemSetting::factory()->phone('(028) 1234 5678')->create();
        SystemSetting::factory()->email('contact@abc.edu.vn')->create();
        SystemSetting::create([
            'setting_key' => 'Website',
            'setting_value' => 'https://abc.edu.vn',
        ]);

        // Create damage reasons
        DamageReason::factory()->withReason('Rách phôi', 'Phôi bị rách trong quá trình sử dụng')->create();
        DamageReason::factory()->withReason('Ố vàng', 'Phôi bị ố vàng do bảo quản không tốt')->create();
        DamageReason::factory()->withReason('Lỗi in ấn', 'Phôi có lỗi trong quá trình in ấn')->create();
        DamageReason::factory()->withReason('Thủng lỗ', 'Phôi bị thủng lỗ')->create();

        $this->command->info('Database seeding completed successfully!');
        $this->command->info('Admin user: admin / password');
        $this->command->info('Diploma manager: diploma_manager / password');
    }
}
