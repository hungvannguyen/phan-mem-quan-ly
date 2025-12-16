<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Major;
use App\Models\DiplomaBlankType;
use App\Models\DiplomaBlankImport;
use App\Models\DiplomaBlank;
use App\Models\Student;
use App\Models\Degree;
use App\Models\SystemSetting;
use App\Models\DamageReason;
use App\Enums\StudentGender;
use App\Enums\StudentStatus;
use App\Enums\DiplomaBlankStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DevelopmentSeeder extends Seeder
{
    /**
     * Seed the database for development/local environment.
     * Creates comprehensive demo data with proper relationships.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Roles and Permissions
            $this->seedRolesAndPermissions();

            // 2. Users
            $this->seedUsers();

            // 3. Majors
            $this->seedMajors();

            // 4. Diploma Blank Types
            $this->seedDiplomaBlankTypes();

            // 5. Diploma Blank Imports (MUST come before blanks)
            $this->seedDiplomaBlankImports();

            // 6. Students
            $this->seedStudents();

            // 7. Degrees (using blanks from imports)
            $this->seedDegrees();

            // 8. System Settings
            $this->seedSystemSettings();

            // 9. Damage Reasons
            $this->seedDamageReasons();
        });
    }

    private function seedRolesAndPermissions(): void
    {
        // Create roles first (PermissionSeeder expects these role names)
        Role::create(['role_name' => 'Admin', 'description' => 'Quản trị viên hệ thống']);
        Role::create(['role_name' => 'Quản lý phôi', 'description' => 'Quản lý phôi văn bằng']);
        Role::create(['role_name' => 'Quản lý văn bằng', 'description' => 'Quản lý văn bằng, chứng chỉ']);
        Role::create(['role_name' => 'Quản lý chứng chỉ', 'description' => 'Quản lý chứng chỉ']);
        Role::create(['role_name' => 'Tra cứu', 'description' => 'Chỉ xem và tra cứu']);

        // Call PermissionSeeder to create permissions and assign to roles
        $this->call(PermissionSeeder::class);
    }

    private function seedUsers(): void
    {
        $adminRole = Role::where('role_name', 'Admin')->first();
        $diplomaBlankManagerRole = Role::where('role_name', 'Quản lý phôi')->first();
        $diplomaManagerRole = Role::where('role_name', 'Quản lý văn bằng')->first();
        $viewerRole = Role::where('role_name', 'Tra cứu')->first();

        // Admin user
        $admin = User::create([
            'full_name' => 'Quản trị viên',
            'email' => 'admin@hvannd.edu.vn',
            'password' => Hash::make('Admin@123456'),
            'is_active' => true,
        ]);
        $admin->roles()->attach($adminRole->role_id);

        // Diploma blank manager
        $diplomaBlankManager = User::create([
            'full_name' => 'Nguyễn Văn Phôi',
            'email' => 'phoi@hvannd.edu.vn',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $diplomaBlankManager->roles()->attach($diplomaBlankManagerRole->role_id);

        // Diploma manager
        $diplomaManager = User::create([
            'full_name' => 'Nguyễn Thị Văn Bằng',
            'email' => 'vanbang@hvannd.edu.vn',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $diplomaManager->roles()->attach($diplomaManagerRole->role_id);

        // Viewer
        $viewer = User::create([
            'full_name' => 'Trần Văn Tra Cứu',
            'email' => 'tracuu@hvannd.edu.vn',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $viewer->roles()->attach($viewerRole->role_id);
    }

    private function seedMajors(): void
    {
        $majors = [
            ['major_name' => 'Công nghệ thông tin', 'major_code' => 'CNTT'],
            ['major_name' => 'Kế toán', 'major_code' => 'KT'],
            ['major_name' => 'Quản trị kinh doanh', 'major_code' => 'QTKD'],
            ['major_name' => 'Luật kinh tế', 'major_code' => 'LKT'],
            ['major_name' => 'Ngôn ngữ Anh', 'major_code' => 'NNA'],
            ['major_name' => 'Du lịch', 'major_code' => 'DL'],
            ['major_name' => 'Tài chính - Ngân hàng', 'major_code' => 'TCNH'],
            ['major_name' => 'Marketing', 'major_code' => 'MKT'],
            ['major_name' => 'Quản lý đất đai', 'major_code' => 'QLDD'],
            ['major_name' => 'Xây dựng dân dụng', 'major_code' => 'XDDD'],
        ];

        foreach ($majors as $major) {
            Major::factory()->withName($major['major_name'], $major['major_code'])->create();
        }
    }

    private function seedDiplomaBlankTypes(): void
    {
        $types = [
            // Bằng tốt nghiệp
            ['type_name' => 'Bằng Cử nhân', 'prefix' => 'BCN'],
            ['type_name' => 'Bằng Kỹ sư', 'prefix' => 'BKS'],
            ['type_name' => 'Bằng Thạc sĩ', 'prefix' => 'BTS'],
            ['type_name' => 'Bằng Tiến sĩ', 'prefix' => 'BTSI'],
            ['type_name' => 'Bằng Trung cấp lý luận chính trị', 'prefix' => 'TC-LLCT'],
            ['type_name' => 'Bằng Cao cấp lý luận chính trị', 'prefix' => 'CC-LLCT'],

            // Chứng chỉ
            ['type_name' => 'Chứng chỉ Nghiệp vụ 6 tháng', 'prefix' => 'NV-6T'],
            ['type_name' => 'Chứng chỉ Trình độ TC lý luận chính trị', 'prefix' => 'TD-TC-LLCT'],
            ['type_name' => 'Chứng chỉ Quân sự-Võ thuật 45 ngày', 'prefix' => 'QSVT-45N'],
            ['type_name' => 'Chứng chỉ Bổ sung kiến thức', 'prefix' => 'BSKT'],
            ['type_name' => 'Chứng chỉ Bồi dưỡng khác', 'prefix' => 'BD-KHAC'],
        ];

        foreach ($types as $type) {
            DiplomaBlankType::create($type);
        }
    }

    private function seedDiplomaBlankImports(): void
    {
        $bachelorType = DiplomaBlankType::where('prefix', 'BCN')->first();
        $engineerType = DiplomaBlankType::where('prefix', 'BKS')->first();
        $masterType = DiplomaBlankType::where('prefix', 'BTS')->first();
        $doctorType = DiplomaBlankType::where('prefix', 'BTSI')->first();
        $intermediatePoliticalType = DiplomaBlankType::where('prefix', 'TC-LLCT')->first();
        $advancedPoliticalType = DiplomaBlankType::where('prefix', 'CC-LLCT')->first();
        $sixMonthCertType = DiplomaBlankType::where('prefix', 'NV-6T')->first();

        // Import 1: 50 Bằng Cử nhân
        $import1 = DiplomaBlankImport::create([
            'type_id' => $bachelorType->type_id,
            'document_reference' => 'CV-HVANND-2023-001',
            'issue_date' => now()->subMonths(12),
            'import_date' => now()->subMonths(12),
            'total_quantity' => 50,
            'prefix' => 'IMP1',
            'from_number' => '00001',
            'to_number' => '00050',
            'status' => 2, // completed
            'processed_count' => 50,
        ]);

        // Create 50 blanks for import 1
        for ($i = 1; $i <= 50; $i++) {
            DiplomaBlank::create([
                'type_id' => $bachelorType->type_id,
                'import_id' => $import1->id,
                'serial_number' => 'IMP1-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'status' => DiplomaBlankStatus::IN_STOCK->value,
            ]);
        }

        // Import 2: 60 Bằng Cử nhân
        $import2 = DiplomaBlankImport::create([
            'type_id' => $bachelorType->type_id,
            'document_reference' => 'CV-HVANND-2024-002',
            'issue_date' => now()->subMonths(6),
            'import_date' => now()->subMonths(6),
            'total_quantity' => 60,
            'prefix' => 'IMP2',
            'from_number' => '00001',
            'to_number' => '00060',
            'status' => 2, // completed
            'processed_count' => 60,
        ]);

        // Create 60 blanks for import 2
        for ($i = 1; $i <= 60; $i++) {
            DiplomaBlank::create([
                'type_id' => $bachelorType->type_id,
                'import_id' => $import2->id,
                'serial_number' => 'IMP2-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'status' => DiplomaBlankStatus::IN_STOCK->value,
            ]);
        }

        // Import 3: 40 Bằng Thạc sĩ
        $import3 = DiplomaBlankImport::create([
            'type_id' => $masterType->type_id,
            'document_reference' => 'CV-HVANND-2024-003',
            'issue_date' => now()->subMonths(3),
            'import_date' => now()->subMonths(3),
            'total_quantity' => 40,
            'prefix' => 'IMP3',
            'from_number' => '00001',
            'to_number' => '00040',
            'status' => 2, // completed
            'processed_count' => 40,
        ]);

        // Create 30 Master blanks
        for ($i = 1; $i <= 30; $i++) {
            DiplomaBlank::create([
                'type_id' => $masterType->type_id,
                'import_id' => $import3->id,
                'serial_number' => 'IMP3-MS-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'status' => DiplomaBlankStatus::IN_STOCK->value,
            ]);
        }

        // Import 4: 40 Bằng Kỹ sư
        $import4 = DiplomaBlankImport::create([
            'type_id' => $engineerType->type_id,
            'document_reference' => 'CV-HVANND-2024-004',
            'issue_date' => now()->subMonths(10),
            'import_date' => now()->subMonths(10),
            'total_quantity' => 40,
            'prefix' => 'IMP4',
            'from_number' => '00001',
            'to_number' => '00040',
            'status' => 2, // completed
            'processed_count' => 40,
        ]);

        for ($i = 1; $i <= 40; $i++) {
            DiplomaBlank::create([
                'type_id' => $engineerType->type_id,
                'import_id' => $import4->id,
                'serial_number' => 'IMP4-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'status' => DiplomaBlankStatus::IN_STOCK->value,
            ]);
        }

        // Import 5: 30 Bằng Trung cấp lý luận chính trị
        $import5 = DiplomaBlankImport::create([
            'type_id' => $intermediatePoliticalType->type_id,
            'document_reference' => 'CV-HVANND-2024-005',
            'issue_date' => now()->subMonths(8),
            'import_date' => now()->subMonths(8),
            'total_quantity' => 30,
            'prefix' => 'IMP5',
            'from_number' => '00001',
            'to_number' => '00030',
            'status' => 2, // completed
            'processed_count' => 30,
        ]);

        for ($i = 1; $i <= 30; $i++) {
            DiplomaBlank::create([
                'type_id' => $intermediatePoliticalType->type_id,
                'import_id' => $import5->id,
                'serial_number' => 'IMP5-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'status' => DiplomaBlankStatus::IN_STOCK->value,
            ]);
        }

        // Import 6: 20 Bằng Cao cấp lý luận chính trị
        $import6 = DiplomaBlankImport::create([
            'type_id' => $advancedPoliticalType->type_id,
            'document_reference' => 'CV-HVANND-2024-006',
            'issue_date' => now()->subMonths(7),
            'import_date' => now()->subMonths(7),
            'total_quantity' => 20,
            'prefix' => 'IMP6',
            'from_number' => '00001',
            'to_number' => '00020',
            'status' => 2, // completed
            'processed_count' => 20,
        ]);

        for ($i = 1; $i <= 20; $i++) {
            DiplomaBlank::create([
                'type_id' => $advancedPoliticalType->type_id,
                'import_id' => $import6->id,
                'serial_number' => 'IMP6-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'status' => DiplomaBlankStatus::IN_STOCK->value,
            ]);
        }

        // Import 7: 50 Chứng chỉ Nghiệp vụ 6 tháng
        $import7 = DiplomaBlankImport::create([
            'type_id' => $sixMonthCertType->type_id,
            'document_reference' => 'CV-HVANND-2024-007',
            'issue_date' => now()->subMonths(5),
            'import_date' => now()->subMonths(5),
            'total_quantity' => 50,
            'prefix' => 'IMP7',
            'from_number' => '00001',
            'to_number' => '00050',
            'status' => 2, // completed
            'processed_count' => 50,
        ]);

        for ($i = 1; $i <= 50; $i++) {
            DiplomaBlank::create([
                'type_id' => $sixMonthCertType->type_id,
                'import_id' => $import7->id,
                'serial_number' => 'IMP7-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'status' => DiplomaBlankStatus::IN_STOCK->value,
            ]);
        }

        // Import 8: 10 Bằng Tiến sĩ (thêm)
        $import8 = DiplomaBlankImport::create([
            'type_id' => $doctorType->type_id,
            'document_reference' => 'CV-HVANND-2024-008',
            'issue_date' => now()->subMonths(4),
            'import_date' => now()->subMonths(4),
            'total_quantity' => 10,
            'prefix' => 'IMP8',
            'from_number' => '00001',
            'to_number' => '00010',
            'status' => 2, // completed
            'processed_count' => 10,
        ]);

        for ($i = 1; $i <= 10; $i++) {
            DiplomaBlank::create([
                'type_id' => $doctorType->type_id,
                'import_id' => $import8->id,
                'serial_number' => 'IMP8-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'status' => DiplomaBlankStatus::IN_STOCK->value,
            ]);
        }
    }

    private function seedStudents(): void
    {
        $majors = Major::all();

        // 100 students đã tốt nghiệp (70 sẽ có văn bằng, 30 chưa có văn bằng)
        foreach ($majors as $major) {
            $yearStart = rand(2015, 2019);
            $yearEnd = $yearStart + 4;

            // Tạo khoảng 10 sinh viên đã tốt nghiệp cho mỗi ngành
            Student::factory()
                ->count(10)
                ->graduated()
                ->create([
                    'major_id' => $major->major_id,
                    'class_name' => 'K' . substr($yearStart, 2) . '-' . $major->major_code,
                    'course' => 'K' . substr($yearStart, 2),
                    'academic_year' => $yearStart . ' - ' . $yearEnd,
                ]);
        }

        // 30 students đang học
        foreach ($majors as $major) {
            $yearStart = rand(2021, 2023);
            $yearEnd = $yearStart + 4;

            Student::factory()
                ->count(3)
                ->studying()
                ->create([
                    'major_id' => $major->major_id,
                    'class_name' => 'K' . substr($yearStart, 2) . '-' . $major->major_code,
                    'course' => 'K' . substr($yearStart, 2),
                    'academic_year' => $yearStart . ' - ' . $yearEnd,
                ]);
        }

        // 20 students đã bỏ học
        foreach ($majors as $major) {
            $yearStart = rand(2018, 2021);
            $yearEnd = $yearStart + 4;

            Student::factory()
                ->count(2)
                ->droppedOut()
                ->create([
                    'major_id' => $major->major_id,
                    'class_name' => 'K' . substr($yearStart, 2) . '-' . $major->major_code,
                    'course' => 'K' . substr($yearStart, 2),
                    'academic_year' => $yearStart . ' - ' . $yearEnd,
                ]);
        }
    }

    private function seedDegrees(): void
    {
        // Get graduated students (first 80)
        $graduatedStudents = Student::where('status', StudentStatus::Graduate->value)
            ->limit(70)
            ->get();

        // Get diploma blank types
        $bachelorType = DiplomaBlankType::where('prefix', 'BCN')->first();
        $engineerType = DiplomaBlankType::where('prefix', 'BKS')->first();
        $masterType = DiplomaBlankType::where('prefix', 'BTS')->first();
        $doctorType = DiplomaBlankType::where('prefix', 'BTSI')->first();

        // Get available blanks (InStock status)
        $bachelorBlanks = DiplomaBlank::where('type_id', $bachelorType->type_id)
            ->where('status', DiplomaBlankStatus::IN_STOCK->value)->get();

        $engineerBlanks = DiplomaBlank::where('type_id', $engineerType->type_id)
            ->where('status', DiplomaBlankStatus::IN_STOCK->value)->get();

        $masterBlanks = DiplomaBlank::where('type_id', $masterType->type_id)
            ->where('status', DiplomaBlankStatus::IN_STOCK->value)->get();

        $doctorBlanks = DiplomaBlank::where('type_id', $doctorType->type_id)
            ->where('status', DiplomaBlankStatus::IN_STOCK->value)->get();

        $degreeIndex = 0;

        // Create 40 Bachelor degrees
        foreach ($graduatedStudents->take(40) as $student) {
            if ($degreeIndex >= $bachelorBlanks->count()) break;

            $blank = $bachelorBlanks[$degreeIndex];
            $grantingDate = now()->subMonths(rand(1, 12));

            Degree::factory()
                ->forStudent($student)
                ->withDiplomaBlank($blank)
                ->create([
                    'degree_type' => 'bachelor',
                    'registration_number' => 'CN' . now()->year . str_pad($degreeIndex + 1, 6, '0', STR_PAD_LEFT),
                    'granting_date' => $grantingDate,
                    'graduation_year' => $grantingDate->year,
                    'decision_number' => 'QĐ-HVANND-CN-' . now()->year . '-' . str_pad($degreeIndex + 1, 4, '0', STR_PAD_LEFT),
                    'ranking' => collect(['Giỏi', 'Khá', 'Trung bình'])->random(),
                    'major_id' => $student->major_id,
                ]);

            $blank->update(['status' => DiplomaBlankStatus::ISSUED->value]);
            $degreeIndex++;
        }

        // Create 15 Engineer degrees
        $engineerIndex = 0;
        foreach ($graduatedStudents->skip(40)->take(15) as $student) {
            if ($engineerIndex >= $engineerBlanks->count()) break;

            $blank = $engineerBlanks[$engineerIndex];
            $grantingDate = now()->subMonths(rand(1, 12));

            Degree::factory()
                ->forStudent($student)
                ->withDiplomaBlank($blank)
                ->create([
                    'degree_type' => 'bachelor',
                    'registration_number' => 'KS' . now()->year . str_pad($engineerIndex + 1, 6, '0', STR_PAD_LEFT),
                    'granting_date' => $grantingDate,
                    'graduation_year' => $grantingDate->year,
                    'decision_number' => 'QĐ-HVANND-KS-' . now()->year . '-' . str_pad($engineerIndex + 1, 4, '0', STR_PAD_LEFT),
                    'ranking' => collect(['Giỏi', 'Khá', 'Trung bình'])->random(),
                    'major_id' => $student->major_id,
                ]);

            $blank->update(['status' => DiplomaBlankStatus::ISSUED->value]);
            $engineerIndex++;
        }

        // Create 10 Master degrees with defense_date
        $masterIndex = 0;
        foreach ($graduatedStudents->skip(55)->take(10) as $student) {
            if ($masterIndex >= $masterBlanks->count()) break;

            $blank = $masterBlanks[$masterIndex];
            $grantingDate = now()->subMonths(rand(1, 12));

            Degree::factory()
                ->forStudent($student)
                ->withDiplomaBlank($blank)
                ->create([
                    'degree_type' => 'master',
                    'registration_number' => 'TS' . now()->year . str_pad($masterIndex + 1, 6, '0', STR_PAD_LEFT),
                    'granting_date' => $grantingDate,
                    'graduation_year' => $grantingDate->year,
                    'defense_date' => $grantingDate->copy()->subMonths(rand(1, 3)),
                    'decision_number' => 'QĐ-HVANND-TS-' . now()->year . '-' . str_pad($masterIndex + 1, 4, '0', STR_PAD_LEFT),
                    'ranking' => collect(['Giỏi', 'Khá'])->random(),
                    'major_id' => $student->major_id,
                ]);

            $blank->update(['status' => DiplomaBlankStatus::ISSUED->value]);
            $masterIndex++;
        }

        // Create 5 Doctor degrees with defense_date
        $doctorIndex = 0;
        foreach ($graduatedStudents->skip(65)->take(5) as $student) {
            if ($doctorIndex >= $doctorBlanks->count()) break;

            $blank = $doctorBlanks[$doctorIndex];
            $grantingDate = now()->subMonths(rand(1, 12));

            Degree::factory()
                ->forStudent($student)
                ->withDiplomaBlank($blank)
                ->create([
                    'degree_type' => 'doctor',
                    'registration_number' => 'TSI' . now()->year . str_pad($doctorIndex + 1, 6, '0', STR_PAD_LEFT),
                    'granting_date' => $grantingDate,
                    'graduation_year' => $grantingDate->year,
                    'defense_date' => $grantingDate->copy()->subMonths(rand(3, 6)),
                    'decision_number' => 'QĐ-HVANND-TSI-' . now()->year . '-' . str_pad($doctorIndex + 1, 4, '0', STR_PAD_LEFT),
                    'ranking' => 'Giỏi',
                    'major_id' => $student->major_id,
                ]);

            $blank->update(['status' => DiplomaBlankStatus::ISSUED->value]);
            $doctorIndex++;
        }
    }

    private function seedSystemSettings(): void
    {
        $settings = [
            ['setting_key' => 'school_name', 'setting_value' => 'Học viện An ninh Nhân dân'],
            ['setting_key' => 'school_address', 'setting_value' => 'Số 125 Trần Phú, Văn Quán, Hà Đông, Hà Nội'],
            ['setting_key' => 'school_phone', 'setting_value' => '024-1234-5678'],
            ['setting_key' => 'school_email', 'setting_value' => 'contact@hvannd.edu.vn'],
            ['setting_key' => 'rector_name', 'setting_value' => 'Thiếu tướng, PGS.TS Nguyễn Văn A'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::create($setting);
        }
    }

    private function seedDamageReasons(): void
    {
        $reasons = [
            ['name' => 'Mất phôi trong quá trình vận chuyển', 'description' => 'Phôi bị mất trong quá trình vận chuyển từ nhà cung cấp'],
            ['name' => 'Hư hỏng do in sai thông tin', 'description' => 'Phôi bị in sai thông tin sinh viên, không thể sử dụng'],
            ['name' => 'Hư hỏng vật lý', 'description' => 'Phôi bị rách, nhàu, hỏng trong quá trình bảo quản'],
            ['name' => 'Mất do lỗi hệ thống', 'description' => 'Không tìm thấy phôi trong kho, nghi mất do lỗi quản lý'],
            ['name' => 'Hủy theo quyết định cấp trên', 'description' => 'Phôi bị hủy theo quyết định của cấp trên'],
        ];

        foreach ($reasons as $reason) {
            DamageReason::create($reason);
        }
    }
}