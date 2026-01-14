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
use App\Models\ChangeLog;
use App\Models\SystemSetting;
use App\Models\DamageReason;
use App\Enums\DegreeStatus;
use App\Enums\StudentGender;
use App\Enums\StudentStatus;
use App\Enums\DiplomaBlankStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DevelopmentSeeder extends Seeder
{
    // Constants for seeding quantities
    private const STUDENTS_GRADUATED = 150;
    private const STUDENTS_STUDYING = 30;
    private const STUDENTS_DROPPED_OUT = 20;
    private const DEGREES_TO_ISSUE = 70;
    private const CERTIFICATES_TO_ISSUE = 75;

    // Array to store degrees that need adjustments
    private array $degreesForAdjustment = [];

    /**
     * Seed the database for development/local environment.
     * Creates comprehensive demo data with proper relationships.
     */
    public function run(): void
    {
        // Disable model event logging during seeding to avoid creating incomplete ChangeLogs
        Student::withoutEvents(function () {
            Degree::withoutEvents(function () {
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
            });
        });

        // 10. Create degree adjustments - Outside transaction
        $this->createDegreeAdjustments();

        // 11. Student Updates (to create change logs) - Outside transaction
        $this->call(StudentUpdateSeeder::class);

        // 12. Degree Reissues (lịch sử cấp lại văn bằng) - Outside transaction
        $this->call(DegreeReissueSeeder::class);
    }

    /**
     * Helper method to create diploma blanks for an import.
     */
    private function createDiplomaBlanks(DiplomaBlankImport $import, int $quantity, string $serialPrefix = ''): void
    {
        $blanks = [];
        $prefix = $serialPrefix ?: $import->prefix;

        for ($i = 1; $i <= $quantity; $i++) {
            $blanks[] = [
                'type_id' => $import->type_id,
                'import_id' => $import->id,
                'serial_number' => $prefix . '-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'status' => DiplomaBlankStatus::IN_STOCK,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DiplomaBlank::insert($blanks);
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
        // Get diploma blank types
        $types = [
            'BCN' => DiplomaBlankType::where('prefix', 'BCN')->first(),
            'BKS' => DiplomaBlankType::where('prefix', 'BKS')->first(),
            'BTS' => DiplomaBlankType::where('prefix', 'BTS')->first(),
            'BTSI' => DiplomaBlankType::where('prefix', 'BTSI')->first(),
            'TC-LLCT' => DiplomaBlankType::where('prefix', 'TC-LLCT')->first(),
            'CC-LLCT' => DiplomaBlankType::where('prefix', 'CC-LLCT')->first(),
            'NV-6T' => DiplomaBlankType::where('prefix', 'NV-6T')->first(),
            'TD-TC-LLCT' => DiplomaBlankType::where('prefix', 'TD-TC-LLCT')->first(),
            'QSVT-45N' => DiplomaBlankType::where('prefix', 'QSVT-45N')->first(),
            'BSKT' => DiplomaBlankType::where('prefix', 'BSKT')->first(),
            'BD-KHAC' => DiplomaBlankType::where('prefix', 'BD-KHAC')->first(),
        ];

        // Data-driven import configuration
        $imports = [
            // Bằng tốt nghiệp
            ['type' => 'BCN', 'doc' => '001', 'months' => 12, 'qty' => 50, 'prefix' => 'IMP1'],
            ['type' => 'BCN', 'doc' => '002', 'months' => 6, 'qty' => 60, 'prefix' => 'IMP2'],
            ['type' => 'BTS', 'doc' => '003', 'months' => 3, 'qty' => 30, 'prefix' => 'IMP3'],
            ['type' => 'BKS', 'doc' => '004', 'months' => 10, 'qty' => 40, 'prefix' => 'IMP4'],
            ['type' => 'TC-LLCT', 'doc' => '005', 'months' => 8, 'qty' => 30, 'prefix' => 'IMP5'],
            ['type' => 'CC-LLCT', 'doc' => '006', 'months' => 7, 'qty' => 20, 'prefix' => 'IMP6'],
            ['type' => 'NV-6T', 'doc' => '007', 'months' => 5, 'qty' => 50, 'prefix' => 'IMP7'],
            ['type' => 'BTSI', 'doc' => '008', 'months' => 4, 'qty' => 10, 'prefix' => 'IMP8'],

            // Chứng chỉ
            ['type' => 'NV-6T', 'doc' => '009', 'months' => 2, 'qty' => 50, 'prefix' => 'IMP9'],
            ['type' => 'TD-TC-LLCT', 'doc' => '010', 'months' => 9, 'qty' => 30, 'prefix' => 'IMP10'],
            ['type' => 'QSVT-45N', 'doc' => '011', 'months' => 11, 'qty' => 40, 'prefix' => 'IMP11'],
            ['type' => 'BSKT', 'doc' => '012', 'months' => 13, 'qty' => 35, 'prefix' => 'IMP12'],
            ['type' => 'BD-KHAC', 'doc' => '013', 'months' => 14, 'qty' => 45, 'prefix' => 'IMP13'],
        ];

        foreach ($imports as $idx => $config) {
            $type = $types[$config['type']];
            $quantity = $config['qty'];

            // Create import record
            $import = DiplomaBlankImport::create([
                'type_id' => $type->type_id,
                'document_reference' => 'CV-HVANND-2024-' . $config['doc'],
                'issue_date' => now()->subMonths($config['months']),
                'import_date' => now()->subMonths($config['months']),
                'total_quantity' => $quantity,
                'prefix' => $config['prefix'],
                'from_number' => '00001',
                'to_number' => str_pad($quantity, 5, '0', STR_PAD_LEFT),
                'status' => 2, // completed
                'processed_count' => $quantity,
            ]);

            // Create blanks using helper method
            $this->createDiplomaBlanks($import, $quantity);
        }
    }

    private function seedStudents(): void
    {
        $majors = Major::all();
        $studentsPerMajor = (int)ceil(self::STUDENTS_GRADUATED / $majors->count());
        $studyingPerMajor = (int)ceil(self::STUDENTS_STUDYING / $majors->count());
        $droppedPerMajor = (int)ceil(self::STUDENTS_DROPPED_OUT / $majors->count());

        foreach ($majors as $major) {
            // Graduated students (2015-2019)
            Student::factory()
                ->count($studentsPerMajor)
                ->graduated()
                ->create([
                    'major_id' => $major->major_id,
                    'class_name' => 'K' . rand(15, 19) . '-' . $major->major_code,
                ]);

            // Studying students (2021-2023)
            Student::factory()
                ->count($studyingPerMajor)
                ->studying()
                ->create([
                    'major_id' => $major->major_id,
                    'class_name' => 'K' . rand(21, 23) . '-' . $major->major_code,
                ]);

            // Dropped out students (2018-2021)
            Student::factory()
                ->count($droppedPerMajor)
                ->droppedOut()
                ->create([
                    'major_id' => $major->major_id,
                    'class_name' => 'K' . rand(18, 21) . '-' . $major->major_code,
                ]);
        }
    }

    private function seedDegrees(): void
    {
        $graduatedStudents = Student::with('major')
            ->where('status', StudentStatus::Graduate->value)
            ->limit(self::DEGREES_TO_ISSUE + self::CERTIFICATES_TO_ISSUE)
            ->get();

        // Define degree types configuration
        $degreeConfigs = [
            ['type' => 'BCN', 'count' => 40, 'degree_type' => 'bachelor', 'prefix' => 'CN', 'ranking' => ['Giỏi', 'Khá', 'Trung bình']],
            ['type' => 'BKS', 'count' => 15, 'degree_type' => 'bachelor', 'prefix' => 'KS', 'ranking' => ['Giỏi', 'Khá', 'Trung bình']],
            ['type' => 'BTS', 'count' => 10, 'degree_type' => 'master', 'prefix' => 'TS', 'ranking' => ['Giỏi', 'Khá'], 'has_defense' => true],
            ['type' => 'BTSI', 'count' => 5, 'degree_type' => 'doctor', 'prefix' => 'TSI', 'ranking' => ['Giỏi'], 'has_defense' => true],
        ];

        $studentOffset = 0;

        foreach ($degreeConfigs as $config) {
            $type = DiplomaBlankType::where('prefix', $config['type'])->first();
            $blanks = DiplomaBlank::where('type_id', $type->type_id)
                ->where('status', DiplomaBlankStatus::IN_STOCK)
                ->limit($config['count'])
                ->get();

            foreach ($graduatedStudents->slice($studentOffset, $config['count'])->values() as $index => $student) {
                if ($index >= $blanks->count()) break;

                $blank = $blanks[$index];
                $grantingDate = now()->subMonths(rand(1, 12));

                $degreeData = [
                    'degree_type' => $config['degree_type'],
                    'registration_number' => $config['prefix'] . now()->year . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                    'granting_date' => $grantingDate,
                    'graduation_year' => $grantingDate->year,
                    'graduation_decision_number' => 'QĐ-HVANND-' . $config['prefix'] . '-' . now()->year . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'ranking' => collect($config['ranking'])->random(),
                    'major_id' => $student->major_id,
                    'major_name' => $student->major->major_name,
                ];

                if (isset($config['has_defense']) && $config['has_defense']) {
                    $degreeData['defense_date'] = $grantingDate->copy()->subMonths(rand(1, 6));
                    // Add council decision data for master and doctorate degrees
                    $degreeData['council_decision_number'] = 'QĐ-HĐ-' . $config['prefix'] . '-' . now()->year . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
                    $degreeData['council_decision_date'] = $degreeData['defense_date']->copy()->subMonths(rand(1, 3));
                }

                $degree = Degree::factory()
                    ->forStudent($student)
                    ->withDiplomaBlank($blank)
                    ->create(array_merge($degreeData, [
                        'status' => DegreeStatus::ISSUED, // Đã cấp vì đã có diploma blank
                    ]));

                $blank->update(['status' => DiplomaBlankStatus::ISSUED]);

                // Store degree ID for later adjustment creation
                $this->degreesForAdjustment[] = [
                    'degree_id' => $degree->degree_id,
                    'chance' => 30
                ];
            }

            $studentOffset += $config['count'];
        }

        // Issue certificates for remaining graduated students
        $this->seedCertificates($graduatedStudents->slice($studentOffset, self::CERTIFICATES_TO_ISSUE));
    }

    private function seedCertificates($students): void
    {
        if ($students->isEmpty()) return;

        $certificateConfigs = [
            ['type' => 'NV-6T', 'count' => 25, 'prefix' => 'NV'],
            ['type' => 'TD-TC-LLCT', 'count' => 15, 'prefix' => 'TDTC'],
            ['type' => 'QSVT-45N', 'count' => 15, 'prefix' => 'QSVT'],
            ['type' => 'BSKT', 'count' => 10, 'prefix' => 'BSKT', 'subtypes' => ['Tin học', 'Ngoại ngữ', 'Chuyên môn', 'Chính trị']],
            ['type' => 'BD-KHAC', 'count' => 10, 'prefix' => 'BD', 'subtypes' => ['Giao tiếp', 'Lãnh đạo', 'Văn phòng', 'Quản lý', 'Kỹ năng mềm']],
        ];

        $studentOffset = 0;

        foreach ($certificateConfigs as $config) {
            $type = DiplomaBlankType::where('prefix', $config['type'])->first();
            $blanks = DiplomaBlank::where('type_id', $type->type_id)
                ->where('status', DiplomaBlankStatus::IN_STOCK)
                ->limit($config['count'])
                ->get();

            foreach ($students->slice($studentOffset, $config['count'])->values() as $index => $student) {
                if ($index >= $blanks->count()) break;

                $blank = $blanks[$index];
                $grantingDate = now()->subMonths(rand(1, 18));

                $notes = $type->type_name;
                if (isset($config['subtypes'])) {
                    $notes .= ' - ' . collect($config['subtypes'])->random();
                }

                $degree = Degree::factory()
                    ->forStudent($student)
                    ->withDiplomaBlank($blank)
                    ->create([
                        'degree_type' => 'certificate',
                        'registration_number' => $config['prefix'] . now()->year . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                        'granting_date' => $grantingDate,
                        'graduation_year' => $grantingDate->year,
                        'graduation_decision_number' => 'QĐ-HVANND-CC-' . now()->year . '-' . str_pad($studentOffset + $index + 1, 4, '0', STR_PAD_LEFT),
                        'major_id' => $student->major_id,
                        'major_name' => $student->major->major_name,
                        'notes' => $notes,
                        'status' => DegreeStatus::ISSUED, // Đã cấp
                    ]);

                $blank->update(['status' => DiplomaBlankStatus::ISSUED]);

                // Store degree ID for later adjustment creation
                $this->degreesForAdjustment[] = [
                    'degree_id' => $degree->degree_id,
                    'chance' => 20
                ];
            }

            $studentOffset += $config['count'];
        }
    }

    /**
     * Create sample adjustments for a degree
     */
    private function createAdjustmentsForDegree(Degree $degree): void
    {
        $adminUser = User::where('email', 'admin@hvannd.edu.vn')->first();
        $adjustmentCount = rand(1, 3);

        $adjustmentContents = [
            'Điều chỉnh thông tin do sai sót trong hồ sơ gốc',
            'Cập nhật ngày cấp bằng theo quyết định mới',
            'Thay đổi xếp loại tốt nghiệp theo quyết định của hội đồng',
            'Điều chỉnh họ tên sinh viên theo giấy khai sinh',
            'Cập nhật thông tin ngành đào tạo',
            'Điều chỉnh số hiệu văn bằng',
            'Cập nhật thông tin theo yêu cầu của sinh viên',
            'Thay đổi ngày tốt nghiệp theo hồ sơ đào tạo',
        ];

        $fields = [
            'registration_number' => 'Số đăng ký',
            'ranking' => 'Xếp loại',
            'granting_date' => 'Ngày cấp',
            'major_name' => 'Ngành đào tạo',
        ];

        for ($i = 0; $i < $adjustmentCount; $i++) {
            $adjustmentDate = now()->subDays(rand(10, 365));
            $field = array_rand($fields);

            ChangeLog::create([
                'entity_type' => 'Degree',
                'entity_id' => $degree->degree_id,
                'changed_field' => $field,
                'old_value' => 'Giá trị cũ',
                'new_value' => 'Giá trị mới',
                'change_description' => collect($adjustmentContents)->random(),
                'decision_number' => 'QĐ-DC-' . now()->year . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'decision_date' => $adjustmentDate,
                'changed_by' => $adminUser->user_id,
                'action_type' => 'update',
                'created_at' => $adjustmentDate,
                'updated_at' => $adjustmentDate,
            ]);
        }
    }

    /**
     * Create adjustments for stored degrees (called outside transaction)
     */
    private function createDegreeAdjustments(): void
    {
        if (empty($this->degreesForAdjustment)) {
            return;
        }

        $this->command->info("\nTạo degree adjustments...");

        $created = 0;
        foreach ($this->degreesForAdjustment as $item) {
            if (rand(1, 100) <= $item['chance']) {
                $degree = Degree::find($item['degree_id']);
                if ($degree) {
                    $this->createAdjustmentsForDegree($degree);
                    $created++;
                }
            }
        }

        $this->command->info("✓ Đã tạo adjustments cho {$created} degrees");
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
