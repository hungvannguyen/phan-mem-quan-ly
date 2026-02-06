<?php

namespace Database\Seeders;

use App\Models\DiplomaBlankType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds for production environment.
     * Only creates essential data: Admin account and diploma blank types.
     */
    public function run(): void
    {
        $this->command->info('Running Production Seeder...');

        // 1. Create all roles
        $adminRole = Role::create(['role_name' => 'Admin', 'description' => 'Quản trị viên hệ thống']);
        Role::create(['role_name' => 'Quản lý phôi', 'description' => 'Quản lý phôi văn bằng']);
        Role::create(['role_name' => 'Quản lý văn bằng', 'description' => 'Quản lý văn bằng đã cấp']);
        Role::create(['role_name' => 'Quản lý chứng chỉ', 'description' => 'Quản lý chứng chỉ đã cấp']);
        Role::create(['role_name' => 'Tra cứu', 'description' => 'Xem và tra cứu thông tin']);

        // 2. Seed permissions and assign to roles
        $this->call(PermissionSeeder::class);

        // 3. Create admin user
        $adminUser = User::create([
            'email' => 'admin@hvannd.edu.vn',
            'password' => bcrypt('Admin@123456'),  // Stronger password for production
            'full_name' => 'Quản trị viên',
            'is_active' => true,
        ]);
        $adminUser->roles()->attach($adminRole->role_id);

        // 4. Create diploma blank types
        $types = [
            // Các loại văn bằng
            ['type_name' => 'Bằng Cử nhân', 'prefix' => 'BCN'],
            ['type_name' => 'Bằng Kỹ sư', 'prefix' => 'BKS'],
            ['type_name' => 'Bằng Thạc sĩ', 'prefix' => 'BTS'],
            ['type_name' => 'Bằng Tiến sĩ', 'prefix' => 'BTSI'],
            ['type_name' => 'Bằng Tiến sĩ Kỹ sư', 'prefix' => 'TSKS'],
            ['type_name' => 'Bằng Trung cấp lý luận chính trị', 'prefix' => 'TC-LLCT'],
            ['type_name' => 'Bằng Cao cấp lý luận chính trị', 'prefix' => 'CC-LLCT'],

            // Các loại chứng chỉ
            ['type_name' => 'Chứng chỉ Nghiệp vụ 6 tháng', 'prefix' => 'NV-6T'],
            ['type_name' => 'Chứng chỉ Trình độ TC lý luận chính trị', 'prefix' => 'TD-TC-LLCT'],
            ['type_name' => 'Chứng chỉ Quân sự-Võ thuật 45 ngày', 'prefix' => 'QSVT-45N'],
            ['type_name' => 'Chứng chỉ Bổ sung kiến thức', 'prefix' => 'BSKT'],
            ['type_name' => 'Chứng chỉ Bồi dưỡng khác', 'prefix' => 'BD-KHAC'],
        ];

        foreach ($types as $type) {
            DiplomaBlankType::create($type);
        }

        $this->command->info('');
        $this->command->info('=== Production Seeding Completed ===');
        $this->command->info('Admin Email: admin@hvannd.edu.vn');
        $this->command->info('Admin Password: Admin@123456');
        $this->command->info('');
        $this->command->warn('⚠️  Please change the admin password immediately!');
    }
}
