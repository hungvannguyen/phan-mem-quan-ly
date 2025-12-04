# Hướng Dẫn Hệ Thống Phân Quyền

## Tổng Quan

Hệ thống phân quyền đã được xây dựng với 5 vai trò (roles) và các quyền chi tiết (permissions) cho từng chức năng. Hệ thống cho phép quản lý quyền truy cập linh hoạt và dễ dàng mở rộng.

## Cấu Trúc Database

### Bảng `permissions`

- `permission_id` (Primary Key)
- `name` - Tên quyền (dạng: `module.action`, ví dụ: `diplomas.create`)
- `display_name` - Tên hiển thị tiếng Việt
- `category` - Danh mục (diploma-blanks, diplomas, certificates, users, settings, profile)
- `description` - Mô tả chi tiết quyền
- `created_at`, `updated_at`

### Bảng `role_permissions` (Pivot Table)

- `role_id` (Foreign Key to roles table)
- `permission_id` (Foreign Key to permissions table)
- Unique constraint trên cặp (role_id, permission_id)

## 5 Vai Trò Trong Hệ Thống

### 1. Admin (Quản trị viên)

**Quyền hạn:** Toàn quyền trên hệ thống

- ✅ Tất cả quyền trên diploma blanks (xem, thêm, sửa, xóa, xuất báo cáo)
- ✅ Tất cả quyền trên diplomas (xem, cấp, sửa, xóa, xuất báo cáo)
- ✅ Tất cả quyền trên certificates (xem, cấp, sửa, xóa, xuất báo cáo)
- ✅ Quản lý users (xem, tạo, sửa, xóa, reset password)
- ✅ Quản lý settings (xem, chỉnh sửa)
- ✅ Đổi mật khẩu của mình

**Đặc biệt:** Chỉ Admin mới có quyền xóa (delete) các bản ghi

### 2. Quản lý phôi (Diploma Blank Manager)

**Quyền hạn:** Quản lý phôi văn bằng

- ✅ `diploma-blanks.view` - Xem danh sách phôi
- ✅ `diploma-blanks.create` - Thêm phôi mới
- ✅ `diploma-blanks.edit` - Sửa thông tin phôi
- ✅ `diploma-blanks.delete` - Xóa phôi
- ✅ `diploma-blanks.export` - Xuất báo cáo phôi
- ✅ `profile.change-password` - Đổi mật khẩu của mình

### 3. Quản lý văn bằng (Diploma Manager)

**Quyền hạn:** Quản lý văn bằng

- ✅ `diplomas.view` - Xem danh sách văn bằng
- ✅ `diplomas.create` - Cấp văn bằng mới
- ✅ `diplomas.edit` - Sửa thông tin văn bằng
- ✅ `diplomas.delete` - Xóa văn bằng
- ✅ `diplomas.export` - Xuất báo cáo văn bằng
- ✅ `profile.change-password` - Đổi mật khẩu của mình

### 4. Quản lý chứng chỉ (Certificate Manager)

**Quyền hạn:** Quản lý chứng chỉ

- ✅ `certificates.view` - Xem danh sách chứng chỉ
- ✅ `certificates.create` - Cấp chứng chỉ mới
- ✅ `certificates.edit` - Sửa thông tin chứng chỉ
- ✅ `certificates.delete` - Xóa chứng chỉ
- ✅ `certificates.export` - Xuất báo cáo chứng chỉ
- ✅ `profile.change-password` - Đổi mật khẩu của mình

### 5. Tra cứu (Viewer)

**Quyền hạn:** Chỉ xem (Read-only)

- ✅ `diplomas.view` - Xem danh sách văn bằng
- ✅ `certificates.view` - Xem danh sách chứng chỉ
- ✅ `profile.change-password` - Đổi mật khẩu của mình

## Sử Dụng Trong Code

### 1. Kiểm Tra Quyền Trong Controller

```php
// Kiểm tra quyền cụ thể
if (auth()->user()->hasPermission('diplomas.create')) {
    // Cho phép tạo văn bằng
}

// Kiểm tra vai trò
if (auth()->user()->hasRole('Admin')) {
    // Chỉ admin
}

// Kiểm tra admin
if (auth()->user()->isAdmin()) {
    // Chỉ admin
}
```

### 2. Sử Dụng Middleware Trong Routes

```php
// Bảo vệ route với quyền cụ thể
Route::get('/diplomas', [DiplomaController::class, 'index'])
    ->middleware('auth')
    ->middleware('permission:diplomas.view');

Route::post('/diplomas', [DiplomaController::class, 'store'])
    ->middleware('auth')
    ->middleware('permission:diplomas.create');

Route::delete('/diplomas/{id}', [DiplomaController::class, 'destroy'])
    ->middleware('auth')
    ->middleware('permission:diplomas.delete');
```

### 3. Sử Dụng Blade Directives Trong Views

```blade
{{-- Kiểm tra quyền cụ thể --}}
@can('diplomas.create')
    <a href="{{ route('diplomas.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Cấp văn bằng mới
    </a>
@endcan

@cannot('diplomas.create')
    <p class="text-muted">Bạn không có quyền cấp văn bằng</p>
@endcannot

{{-- Chỉ hiển thị cho Admin --}}
@admin
    <button type="submit" class="btn btn-danger">
        <i class="fas fa-trash"></i> Xóa
    </button>
@endadmin

{{-- Kiểm tra vai trò --}}
@hasRole('Quản lý văn bằng')
    <div class="admin-panel">
        <!-- Nội dung dành cho Quản lý văn bằng -->
    </div>
@endhasRole
```

### 4. Ví Dụ: Ẩn/Hiện Nút Xóa

```blade
<td>
    @can('diplomas.edit')
        <a href="{{ route('diplomas.edit', $diploma->id) }}" class="btn btn-sm btn-warning">
            <i class="fas fa-edit"></i> Sửa
        </a>
    @endcan

    @admin
        <form action="{{ route('diplomas.destroy', $diploma->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger"
                    onclick="return confirm('Bạn có chắc muốn xóa?')">
                <i class="fas fa-trash"></i> Xóa
            </button>
        </form>
    @endadmin
</td>
```

## Danh Sách Tất Cả Permissions

### Diploma Blanks (Phôi văn bằng)

- `diploma-blanks.view` - Xem danh sách phôi
- `diploma-blanks.create` - Thêm phôi mới
- `diploma-blanks.edit` - Sửa thông tin phôi
- `diploma-blanks.delete` - Xóa phôi
- `diploma-blanks.export` - Xuất báo cáo phôi

### Diplomas (Văn bằng)

- `diplomas.view` - Xem danh sách văn bằng
- `diplomas.create` - Cấp văn bằng mới
- `diplomas.edit` - Sửa thông tin văn bằng
- `diplomas.delete` - Xóa văn bằng
- `diplomas.export` - Xuất báo cáo văn bằng

### Certificates (Chứng chỉ)

- `certificates.view` - Xem danh sách chứng chỉ
- `certificates.create` - Cấp chứng chỉ mới
- `certificates.edit` - Sửa thông tin chứng chỉ
- `certificates.delete` - Xóa chứng chỉ
- `certificates.export` - Xuất báo cáo chứng chỉ

### Users (Người dùng)

- `users.view` - Xem danh sách người dùng
- `users.create` - Tạo người dùng mới
- `users.edit` - Sửa thông tin người dùng
- `users.delete` - Xóa người dùng
- `users.reset-password` - Reset mật khẩu người dùng khác

### Settings (Cài đặt hệ thống)

- `settings.view` - Xem cài đặt hệ thống
- `settings.edit` - Chỉnh sửa cài đặt hệ thống

### Profile (Quản lý cá nhân)

- `profile.change-password` - Đổi mật khẩu của mình

## Các Bước Tiếp Theo (TODO)

### 1. Tạo UI Quản Lý Permissions (Trong Settings)

- [ ] Thêm tab "Phân quyền" trong trang Quản lý danh mục
- [ ] Hiển thị ma trận: Vai trò (hàng) × Quyền (cột)
- [ ] Cho phép Admin bật/tắt quyền cho từng vai trò
- [ ] Lưu thay đổi vào bảng `role_permissions`

### 2. Áp Dụng Middleware Cho Routes

- [ ] Thêm middleware `permission` vào các routes trong `routes/web.php`
- [ ] Bảo vệ routes diploma blanks với quyền `diploma-blanks.*`
- [ ] Bảo vệ routes diplomas với quyền `diplomas.*`
- [ ] Bảo vệ routes certificates với quyền `certificates.*`
- [ ] Bảo vệ routes users với quyền `users.*`
- [ ] Bảo vệ routes settings với quyền `settings.*`

### 3. Cập Nhật Views Để Hiển Thị/Ẩn Nút

- [ ] **Diploma Blanks Views:** Ẩn nút Thêm/Sửa/Xóa theo quyền
- [ ] **Diplomas Views:** Ẩn nút Cấp/Sửa/Xóa theo quyền
- [ ] **Certificates Views:** Ẩn nút Cấp/Sửa/Xóa theo quyền
- [ ] **Settings Views:** Ẩn nút Thêm/Sửa/Xóa theo quyền
- [ ] Chỉ hiển thị nút Xóa cho Admin (`@admin`)
- [ ] Hiển thị nút Xuất báo cáo theo quyền `*.export`

### 4. Tạo User Management UI

- [ ] Trang danh sách users
- [ ] Form tạo/sửa user với dropdown chọn role
- [ ] Chức năng reset password (chỉ Admin)
- [ ] Chức năng đổi mật khẩu của mình (tất cả users)

### 5. Testing

- [ ] Test đăng nhập với từng vai trò
- [ ] Kiểm tra hiển thị đúng menu/nút theo quyền
- [ ] Test truy cập trực tiếp URL bị cấm (phải 403)
- [ ] Test Admin có thể xóa, các role khác không thể

## Lưu Ý Quan Trọng

1. **Chỉ Admin có thể xóa:** Tất cả nút "Xóa" phải nằm trong `@admin` directive
2. **Middleware Registration:** Đừng quên đăng ký middleware trong `bootstrap/app.php` hoặc `app/Http/Kernel.php`
3. **Cache Permissions:** Có thể cache permissions để tăng performance
4. **Eager Loading:** Khi query users, nên eager load roles và permissions để tránh N+1 query

## Câu Lệnh Hữu Ích

```bash
# Refresh database và seed lại permissions
php artisan migrate:fresh --seed

# Chỉ chạy PermissionSeeder
php artisan db:seed --class=PermissionSeeder

# Xóa cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## Ví Dụ: Thêm Permission Mới

Nếu bạn muốn thêm permission mới sau này:

1. Thêm vào `PermissionSeeder.php`:

```php
['name' => 'reports.view', 'display_name' => 'Xem báo cáo', 'category' => 'reports', 'description' => 'Có thể xem các báo cáo thống kê'],
```

2. Gán cho role cần thiết:

```php
$adminPermissions = Permission::whereIn('name', [
    // ... existing permissions
    'reports.view',
])->pluck('permission_id');
$admin->permissions()->attach($adminPermissions);
```

3. Chạy lại seeder:

```bash
php artisan db:seed --class=PermissionSeeder
```

---

**Hệ thống phân quyền đã sẵn sàng!** Bạn có thể bắt đầu áp dụng vào các views và controllers của mình.
