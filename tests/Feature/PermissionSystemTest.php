<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Chỉ seed roles và permissions, không seed toàn bộ data
        $this->seedRolesAndPermissions();
    }

    /**
     * Seed only roles and permissions for testing
     */
    protected function seedRolesAndPermissions(): void
    {
        // Create 5 roles
        $adminRole = Role::factory()->admin()->create();
        $diplomaBlankManagerRole = Role::factory()->diplomaBlankManager()->create();
        $diplomaManagerRole = Role::factory()->diplomaManager()->create();
        $certificateManagerRole = Role::factory()->certificateManager()->create();
        $viewerRole = Role::factory()->viewer()->create();

        // Seed permissions
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\PermissionSeeder']);
    }

    /** @test */
    public function test_role_has_permissions_after_seeding()
    {
        // Kiểm tra Admin role có đầy đủ quyền
        $adminRole = Role::where('role_name', 'Admin')->first();
        $this->assertNotNull($adminRole);

        $adminPermissions = $adminRole->permissions;
        $this->assertGreaterThan(0, $adminPermissions->count());

        // Admin phải có 23 permissions
        $this->assertEquals(23, $adminPermissions->count());

        $this->info("✅ Admin role có {$adminPermissions->count()} permissions");
    }

    /** @test */
    public function test_user_can_check_permission_through_role()
    {
        // Tạo Admin role và user
        $adminRole = Role::where('role_name', 'Admin')->first();
        $user = User::factory()->create();
        $user->roles()->attach($adminRole->role_id);

        // Kiểm tra user có quyền
        $this->assertTrue($user->hasPermission('diplomas.create'));
        $this->assertTrue($user->hasPermission('diplomas.delete'));
        $this->assertTrue($user->hasPermission('users.create'));

        $this->info("✅ User với Admin role có thể check permissions");
    }

    /** @test */
    public function test_user_without_permission_cannot_access()
    {
        // Tạo Viewer role (chỉ xem)
        $viewerRole = Role::where('role_name', 'Tra cứu')->first();
        $user = User::factory()->create();
        $user->roles()->attach($viewerRole->role_id);

        // Viewer không được create/delete
        $this->assertFalse($user->hasPermission('diplomas.create'));
        $this->assertFalse($user->hasPermission('diplomas.delete'));
        $this->assertFalse($user->hasPermission('users.create'));

        // Nhưng có thể view
        $this->assertTrue($user->hasPermission('diplomas.view'));

        $this->info("✅ User với Viewer role không có quyền create/delete");
    }

    /** @test */
    public function test_can_grant_permission_to_role()
    {
        // Tạo role mới
        $testRole = Role::create([
            'role_name' => 'Test Role',
            'description' => 'Role for testing'
        ]);

        // Ban đầu không có quyền nào
        $this->assertEquals(0, $testRole->permissions()->count());

        // Cấp quyền diplomas.view
        $permission = Permission::where('name', 'diplomas.view')->first();
        $testRole->permissions()->attach($permission->permission_id);

        // Kiểm tra đã có quyền
        $testRole->refresh();
        $this->assertEquals(1, $testRole->permissions()->count());
        $this->assertTrue($testRole->hasPermission('diplomas.view'));

        $this->info("✅ Có thể cấp quyền cho role");
    }

    /** @test */
    public function test_can_revoke_permission_from_role()
    {
        // Lấy Admin role (có nhiều quyền)
        $adminRole = Role::where('role_name', 'Admin')->first();
        $initialCount = $adminRole->permissions()->count();

        $this->assertGreaterThan(0, $initialCount);

        // Thu hồi quyền diplomas.delete
        $permission = Permission::where('name', 'diplomas.delete')->first();
        $adminRole->permissions()->detach($permission->permission_id);

        // Kiểm tra đã mất quyền
        $adminRole->refresh();
        $this->assertEquals($initialCount - 1, $adminRole->permissions()->count());
        $this->assertFalse($adminRole->hasPermission('diplomas.delete'));

        $this->info("✅ Có thể thu hồi quyền từ role");
    }

    /** @test */
    public function test_user_permission_changes_when_role_permission_changes()
    {
        // Tạo role và user
        $role = Role::create([
            'role_name' => 'Dynamic Role',
            'description' => 'Role with changing permissions'
        ]);

        $user = User::factory()->create();
        $user->roles()->attach($role->role_id);

        // Ban đầu không có quyền
        $this->assertFalse($user->hasPermission('diplomas.create'));

        // Cấp quyền cho role
        $permission = Permission::where('name', 'diplomas.create')->first();
        $role->permissions()->attach($permission->permission_id);

        // User tự động có quyền (cần refresh relationship)
        $user->refresh();
        $this->assertTrue($user->hasPermission('diplomas.create'));

        // Thu hồi quyền
        $role->permissions()->detach($permission->permission_id);

        // User tự động mất quyền
        $user->refresh();
        $this->assertFalse($user->hasPermission('diplomas.create'));

        $this->info("✅ Quyền của user thay đổi theo quyền của role");
    }

    /** @test */
    public function test_diploma_blank_manager_has_correct_permissions()
    {
        $role = Role::where('role_name', 'Quản lý phôi')->first();
        $user = User::factory()->create();
        $user->roles()->attach($role->role_id);

        // Có quyền với diploma-blanks
        $this->assertTrue($user->hasPermission('diploma-blanks.view'));
        $this->assertTrue($user->hasPermission('diploma-blanks.create'));
        $this->assertTrue($user->hasPermission('diploma-blanks.edit'));
        $this->assertTrue($user->hasPermission('diploma-blanks.delete'));
        $this->assertTrue($user->hasPermission('diploma-blanks.export'));

        // Không có quyền với diplomas
        $this->assertFalse($user->hasPermission('diplomas.view'));
        $this->assertFalse($user->hasPermission('diplomas.create'));

        // Không có quyền với users
        $this->assertFalse($user->hasPermission('users.create'));
        $this->assertFalse($user->hasPermission('users.delete'));

        $this->info("✅ Quản lý phôi có đúng 6 permissions về diploma-blanks");
    }

    /** @test */
    public function test_diploma_manager_has_correct_permissions()
    {
        $role = Role::where('role_name', 'Quản lý văn bằng')->first();
        $user = User::factory()->create();
        $user->roles()->attach($role->role_id);

        // Có quyền với diplomas
        $this->assertTrue($user->hasPermission('diplomas.view'));
        $this->assertTrue($user->hasPermission('diplomas.create'));
        $this->assertTrue($user->hasPermission('diplomas.edit'));
        $this->assertTrue($user->hasPermission('diplomas.delete'));
        $this->assertTrue($user->hasPermission('diplomas.export'));

        // Không có quyền với diploma-blanks
        $this->assertFalse($user->hasPermission('diploma-blanks.view'));

        // Không có quyền với certificates
        $this->assertFalse($user->hasPermission('certificates.view'));

        $this->info("✅ Quản lý văn bằng có đúng permissions về diplomas");
    }

    /** @test */
    public function test_certificate_manager_has_correct_permissions()
    {
        $role = Role::where('role_name', 'Quản lý chứng chỉ')->first();
        $user = User::factory()->create();
        $user->roles()->attach($role->role_id);

        // Có quyền với certificates
        $this->assertTrue($user->hasPermission('certificates.view'));
        $this->assertTrue($user->hasPermission('certificates.create'));
        $this->assertTrue($user->hasPermission('certificates.edit'));
        $this->assertTrue($user->hasPermission('certificates.delete'));
        $this->assertTrue($user->hasPermission('certificates.export'));

        // Không có quyền với diplomas
        $this->assertFalse($user->hasPermission('diplomas.view'));

        // Không có quyền với diploma-blanks
        $this->assertFalse($user->hasPermission('diploma-blanks.view'));

        $this->info("✅ Quản lý chứng chỉ có đúng permissions về certificates");
    }

    /** @test */
    public function test_viewer_has_only_read_permissions()
    {
        $role = Role::where('role_name', 'Tra cứu')->first();
        $user = User::factory()->create();
        $user->roles()->attach($role->role_id);

        // Chỉ có quyền view
        $this->assertTrue($user->hasPermission('diplomas.view'));
        $this->assertTrue($user->hasPermission('certificates.view'));
        $this->assertTrue($user->hasPermission('profile.change-password'));

        // Không có quyền create/edit/delete
        $this->assertFalse($user->hasPermission('diplomas.create'));
        $this->assertFalse($user->hasPermission('diplomas.edit'));
        $this->assertFalse($user->hasPermission('diplomas.delete'));
        $this->assertFalse($user->hasPermission('certificates.create'));
        $this->assertFalse($user->hasPermission('diploma-blanks.view'));

        $this->info("✅ Viewer chỉ có 3 permissions (view diplomas, view certificates, change password)");
    }

    /** @test */
    public function test_only_admin_has_user_management_permissions()
    {
        // Admin có quyền quản lý users
        $adminRole = Role::where('role_name', 'Admin')->first();
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->role_id);

        $this->assertTrue($admin->hasPermission('users.create'));
        $this->assertTrue($admin->hasPermission('users.edit'));
        $this->assertTrue($admin->hasPermission('users.delete'));
        $this->assertTrue($admin->hasPermission('users.reset-password'));

        // Các role khác không có
        $diplomaManagerRole = Role::where('role_name', 'Quản lý văn bằng')->first();
        $manager = User::factory()->create();
        $manager->roles()->attach($diplomaManagerRole->role_id);

        $this->assertFalse($manager->hasPermission('users.create'));
        $this->assertFalse($manager->hasPermission('users.delete'));

        $this->info("✅ Chỉ Admin có quyền quản lý users");
    }

    /** @test */
    public function test_only_admin_has_settings_permissions()
    {
        // Admin có quyền settings
        $adminRole = Role::where('role_name', 'Admin')->first();
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->role_id);

        $this->assertTrue($admin->hasPermission('settings.view'));
        $this->assertTrue($admin->hasPermission('settings.edit'));

        // Các role khác không có
        $viewerRole = Role::where('role_name', 'Tra cứu')->first();
        $viewer = User::factory()->create();
        $viewer->roles()->attach($viewerRole->role_id);

        $this->assertFalse($viewer->hasPermission('settings.view'));
        $this->assertFalse($viewer->hasPermission('settings.edit'));

        $this->info("✅ Chỉ Admin có quyền quản lý settings");
    }

    /** @test */
    public function test_all_users_can_change_own_password()
    {
        $roles = Role::all();

        foreach ($roles as $role) {
            $user = User::factory()->create();
            $user->roles()->attach($role->role_id);

            $this->assertTrue(
                $user->hasPermission('profile.change-password'),
                "Role {$role->role_name} không có quyền đổi mật khẩu"
            );
        }

        $this->info("✅ Tất cả 5 roles đều có quyền đổi mật khẩu của mình");
    }

    /** @test */
    public function test_is_admin_method_works_correctly()
    {
        // Admin user
        $adminRole = Role::where('role_name', 'Admin')->first();
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->role_id);

        $this->assertTrue($admin->isAdmin());

        // Non-admin user
        $viewerRole = Role::where('role_name', 'Tra cứu')->first();
        $viewer = User::factory()->create();
        $viewer->roles()->attach($viewerRole->role_id);

        $this->assertFalse($viewer->isAdmin());

        $this->info("✅ Method isAdmin() hoạt động đúng");
    }

    /** @test */
    public function test_user_with_multiple_roles_has_combined_permissions()
    {
        // Tạo user với 2 roles
        $diplomaRole = Role::where('role_name', 'Quản lý văn bằng')->first();
        $certificateRole = Role::where('role_name', 'Quản lý chứng chỉ')->first();

        $user = User::factory()->create();
        $user->roles()->attach([$diplomaRole->role_id, $certificateRole->role_id]);

        // Có quyền từ cả 2 roles
        $this->assertTrue($user->hasPermission('diplomas.create'));
        $this->assertTrue($user->hasPermission('certificates.create'));

        // Không có quyền của role khác
        $this->assertFalse($user->hasPermission('diploma-blanks.create'));
        $this->assertFalse($user->hasPermission('users.create'));

        $this->info("✅ User với nhiều roles có quyền tổng hợp từ tất cả roles");
    }

    /** @test */
    public function test_permission_check_with_non_existent_permission()
    {
        $adminRole = Role::where('role_name', 'Admin')->first();
        $user = User::factory()->create();
        $user->roles()->attach($adminRole->role_id);

        // Kiểm tra quyền không tồn tại
        $this->assertFalse($user->hasPermission('non.existent.permission'));

        $this->info("✅ Kiểm tra quyền không tồn tại trả về false");
    }

    /** @test */
    public function test_user_without_role_has_no_permissions()
    {
        // User không có role nào
        $user = User::factory()->create();

        $this->assertFalse($user->hasPermission('diplomas.view'));
        $this->assertFalse($user->hasPermission('diplomas.create'));
        $this->assertFalse($user->isAdmin());

        $this->info("✅ User không có role không có quyền gì");
    }

    /**
     * Helper method to output info messages
     */
    protected function info(string $message): void
    {
        fwrite(STDOUT, $message . "\n");
    }
}
