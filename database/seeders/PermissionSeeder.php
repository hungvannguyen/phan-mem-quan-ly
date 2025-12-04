<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all permissions
        $permissions = [
            // Diploma Blanks permissions
            ['name' => 'diploma-blanks.view', 'display_name' => 'Xem danh sách phôi', 'category' => 'diploma-blanks', 'description' => 'Có thể xem danh sách phôi văn bằng'],
            ['name' => 'diploma-blanks.create', 'display_name' => 'Thêm phôi mới', 'category' => 'diploma-blanks', 'description' => 'Có thể thêm phôi văn bằng mới'],
            ['name' => 'diploma-blanks.edit', 'display_name' => 'Sửa thông tin phôi', 'category' => 'diploma-blanks', 'description' => 'Có thể chỉnh sửa thông tin phôi'],
            ['name' => 'diploma-blanks.delete', 'display_name' => 'Xóa phôi', 'category' => 'diploma-blanks', 'description' => 'Có thể xóa phôi văn bằng'],
            ['name' => 'diploma-blanks.export', 'display_name' => 'Xuất báo cáo phôi', 'category' => 'diploma-blanks', 'description' => 'Có thể xuất báo cáo về phôi'],

            // Diplomas permissions
            ['name' => 'diplomas.view', 'display_name' => 'Xem danh sách văn bằng', 'category' => 'diplomas', 'description' => 'Có thể xem danh sách văn bằng'],
            ['name' => 'diplomas.create', 'display_name' => 'Cấp văn bằng mới', 'category' => 'diplomas', 'description' => 'Có thể cấp văn bằng mới cho sinh viên'],
            ['name' => 'diplomas.edit', 'display_name' => 'Sửa thông tin văn bằng', 'category' => 'diplomas', 'description' => 'Có thể chỉnh sửa thông tin văn bằng'],
            ['name' => 'diplomas.delete', 'display_name' => 'Xóa văn bằng', 'category' => 'diplomas', 'description' => 'Có thể xóa văn bằng đã cấp'],
            ['name' => 'diplomas.export', 'display_name' => 'Xuất báo cáo văn bằng', 'category' => 'diplomas', 'description' => 'Có thể xuất báo cáo về văn bằng'],

            // Certificates permissions
            ['name' => 'certificates.view', 'display_name' => 'Xem danh sách chứng chỉ', 'category' => 'certificates', 'description' => 'Có thể xem danh sách chứng chỉ'],
            ['name' => 'certificates.create', 'display_name' => 'Cấp chứng chỉ mới', 'category' => 'certificates', 'description' => 'Có thể cấp chứng chỉ mới'],
            ['name' => 'certificates.edit', 'display_name' => 'Sửa thông tin chứng chỉ', 'category' => 'certificates', 'description' => 'Có thể chỉnh sửa thông tin chứng chỉ'],
            ['name' => 'certificates.delete', 'display_name' => 'Xóa chứng chỉ', 'category' => 'certificates', 'description' => 'Có thể xóa chứng chỉ đã cấp'],
            ['name' => 'certificates.export', 'display_name' => 'Xuất báo cáo chứng chỉ', 'category' => 'certificates', 'description' => 'Có thể xuất báo cáo về chứng chỉ'],

            // Users permissions
            ['name' => 'users.view', 'display_name' => 'Xem danh sách người dùng', 'category' => 'users', 'description' => 'Có thể xem danh sách người dùng'],
            ['name' => 'users.create', 'display_name' => 'Tạo người dùng mới', 'category' => 'users', 'description' => 'Có thể tạo tài khoản người dùng mới'],
            ['name' => 'users.edit', 'display_name' => 'Sửa thông tin người dùng', 'category' => 'users', 'description' => 'Có thể chỉnh sửa thông tin người dùng'],
            ['name' => 'users.delete', 'display_name' => 'Xóa người dùng', 'category' => 'users', 'description' => 'Có thể xóa tài khoản người dùng'],
            ['name' => 'users.reset-password', 'display_name' => 'Reset mật khẩu', 'category' => 'users', 'description' => 'Có thể reset mật khẩu người dùng khác'],

            // Settings permissions
            ['name' => 'settings.view', 'display_name' => 'Xem cài đặt hệ thống', 'category' => 'settings', 'description' => 'Có thể xem các thiết lập hệ thống'],
            ['name' => 'settings.edit', 'display_name' => 'Chỉnh sửa cài đặt', 'category' => 'settings', 'description' => 'Có thể thay đổi cài đặt hệ thống'],

            // Self management permissions
            ['name' => 'profile.change-password', 'display_name' => 'Đổi mật khẩu của mình', 'category' => 'profile', 'description' => 'Có thể thay đổi mật khẩu của chính mình'],
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Get all roles
        $admin = Role::where('role_name', 'Admin')->first();
        $diplomaBlankManager = Role::where('role_name', 'Quản lý phôi')->first();
        $diplomaManager = Role::where('role_name', 'Quản lý văn bằng')->first();
        $certificateManager = Role::where('role_name', 'Quản lý chứng chỉ')->first();
        $viewer = Role::where('role_name', 'Tra cứu')->first();

        // Assign permissions to Admin (full access)
        $adminPermissions = Permission::all()->pluck('permission_id');
        $admin->permissions()->attach($adminPermissions);

        // Assign permissions to Diploma Blank Manager
        $diplomaBlankPermissions = Permission::whereIn('name', [
            'diploma-blanks.view',
            'diploma-blanks.create',
            'diploma-blanks.edit',
            'diploma-blanks.delete',
            'diploma-blanks.export',
            'profile.change-password',
        ])->pluck('permission_id');
        $diplomaBlankManager->permissions()->attach($diplomaBlankPermissions);

        // Assign permissions to Diploma Manager
        $diplomaPermissions = Permission::whereIn('name', [
            'diplomas.view',
            'diplomas.create',
            'diplomas.edit',
            'diplomas.delete',
            'diplomas.export',
            'profile.change-password',
        ])->pluck('permission_id');
        $diplomaManager->permissions()->attach($diplomaPermissions);

        // Assign permissions to Certificate Manager
        $certificatePermissions = Permission::whereIn('name', [
            'certificates.view',
            'certificates.create',
            'certificates.edit',
            'certificates.delete',
            'certificates.export',
            'profile.change-password',
        ])->pluck('permission_id');
        $certificateManager->permissions()->attach($certificatePermissions);

        // Assign permissions to Viewer (read-only)
        $viewerPermissions = Permission::whereIn('name', [
            'diplomas.view',
            'certificates.view',
            'profile.change-password',
        ])->pluck('permission_id');
        $viewer->permissions()->attach($viewerPermissions);
    }
}
