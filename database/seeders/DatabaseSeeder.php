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
            'password_hash' => bcrypt('password'),
            'full_name' => 'Quản trị viên',
            'email' => 'admin@example.com',
            'is_active' => true,
        ]);
        $adminUser->roles()->attach($adminRole->role_id);

        // Create diploma manager user
        $diplomaUser = User::factory()->create([
            'username' => 'diploma_manager',
            'password_hash' => bcrypt('password'),
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
        $majors = Major::factory()->count(10)->create();

        // Create students
        Student::factory(50)->create()->each(function ($student) use ($majors) {
            $student->update(['major_id' => $majors->random()->major_id]);
        });

        // Create diploma blank types
        $universityType = DiplomaBlankType::factory()->university()->create();
        $collegeType = DiplomaBlankType::factory()->college()->create();
        DiplomaBlankType::factory()->create([
            'type_name' => 'Chứng chỉ Tin học',
            'prefix' => 'TH',
        ]);
        DiplomaBlankType::factory()->create([
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
            ->each(function ($blank) use ($universityType, $collegeType) {
                $blank->update([
                    'type_id' => collect([$universityType->type_id, $collegeType->type_id])->random()
                ]);
            });
        $diplomaBlanks = $diplomaBlanks->merge($availableBlanks);

        // Create issued diploma blanks
        $issuedBlanks = DiplomaBlank::factory()
            ->count(30)
            ->issued()
            ->create()
            ->each(function ($blank) use ($universityType, $collegeType) {
                $blank->update([
                    'type_id' => collect([$universityType->type_id, $collegeType->type_id])->random()
                ]);
            });
        $diplomaBlanks = $diplomaBlanks->merge($issuedBlanks);

        // Create damaged diploma blanks
        DiplomaBlank::factory()
            ->count(10)
            ->damaged()
            ->create()
            ->each(function ($blank) use ($universityType, $collegeType) {
                $blank->update([
                    'type_id' => collect([$universityType->type_id, $collegeType->type_id])->random()
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
