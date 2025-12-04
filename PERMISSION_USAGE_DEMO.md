# 🧪 Demo: Sử Dụng Hệ Thống Phân Quyền

## 1️⃣ Cấp Quyền Cho Role

```php
// Trong Controller hoặc Seeder
use App\Models\Role;
use App\Models\Permission;

// Lấy role cần cấp quyền
$role = Role::where('role_name', 'Quản lý văn bằng')->first();

// Lấy permission cần cấp
$permission = Permission::where('name', 'diplomas.export')->first();

// Cấp quyền cho role
$role->permissions()->attach($permission->permission_id);

// Hoặc cấp nhiều quyền cùng lúc
$permissions = Permission::whereIn('name', [
    'diplomas.view',
    'diplomas.create',
    'diplomas.edit'
])->pluck('permission_id');

$role->permissions()->attach($permissions);

echo "✅ Đã cấp quyền cho role {$role->role_name}";
```

---

## 2️⃣ Thu Hồi Quyền Từ Role

```php
use App\Models\Role;
use App\Models\Permission;

// Lấy role cần thu hồi quyền
$role = Role::where('role_name', 'Quản lý văn bằng')->first();

// Lấy permission cần thu hồi
$permission = Permission::where('name', 'diplomas.delete')->first();

// Thu hồi quyền
$role->permissions()->detach($permission->permission_id);

echo "✅ Đã thu hồi quyền {$permission->display_name} từ role {$role->role_name}";

// Thu hồi TẤT CẢ quyền
$role->permissions()->detach();
echo "✅ Đã thu hồi tất cả quyền từ role {$role->role_name}";
```

---

## 3️⃣ Kiểm Tra Quyền Trong Controller

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DiplomaController extends Controller
{
    public function index()
    {
        // Kiểm tra quyền xem
        if (!auth()->user()->hasPermission('diplomas.view')) {
            abort(403, 'Bạn không có quyền xem danh sách văn bằng');
        }

        $diplomas = Diploma::paginate(20);
        return view('diplomas.index', compact('diplomas'));
    }

    public function create()
    {
        // Kiểm tra quyền tạo
        if (!auth()->user()->hasPermission('diplomas.create')) {
            return redirect()
                ->route('diplomas.index')
                ->with('error', 'Bạn không có quyền cấp văn bằng mới');
        }

        return view('diplomas.create');
    }

    public function destroy($id)
    {
        // Chỉ Admin mới có thể xóa
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Chỉ Admin mới có quyền xóa văn bằng');
        }

        $diploma = Diploma::findOrFail($id);
        $diploma->delete();

        return redirect()
            ->route('diplomas.index')
            ->with('success', 'Đã xóa văn bằng thành công');
    }

    public function export()
    {
        // Kiểm tra quyền export
        if (!auth()->user()->hasPermission('diplomas.export')) {
            abort(403, 'Bạn không có quyền xuất báo cáo');
        }

        // Logic export...
        return Excel::download(new DiplomasExport, 'diplomas.xlsx');
    }
}
```

---

## 4️⃣ Bảo Vệ Routes Với Middleware

```php
// routes/web.php

use App\Http\Controllers\DiplomaController;
use App\Http\Controllers\DiplomaBlankController;
use App\Http\Controllers\UserController;

Route::middleware(['auth'])->group(function () {

    // Diplomas routes
    Route::get('/diplomas', [DiplomaController::class, 'index'])
        ->middleware('permission:diplomas.view');

    Route::get('/diplomas/create', [DiplomaController::class, 'create'])
        ->middleware('permission:diplomas.create');

    Route::post('/diplomas', [DiplomaController::class, 'store'])
        ->middleware('permission:diplomas.create');

    Route::get('/diplomas/{id}/edit', [DiplomaController::class, 'edit'])
        ->middleware('permission:diplomas.edit');

    Route::put('/diplomas/{id}', [DiplomaController::class, 'update'])
        ->middleware('permission:diplomas.edit');

    Route::delete('/diplomas/{id}', [DiplomaController::class, 'destroy'])
        ->middleware('permission:diplomas.delete');

    Route::get('/diplomas/export', [DiplomaController::class, 'export'])
        ->middleware('permission:diplomas.export');

    // Diploma Blanks routes
    Route::get('/diploma-blanks', [DiplomaBlankController::class, 'index'])
        ->middleware('permission:diploma-blanks.view');

    Route::post('/diploma-blanks', [DiplomaBlankController::class, 'store'])
        ->middleware('permission:diploma-blanks.create');

    // Users routes - Chỉ Admin
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:users.view');

    Route::post('/users', [UserController::class, 'store'])
        ->middleware('permission:users.create');

    Route::delete('/users/{id}', [UserController::class, 'destroy'])
        ->middleware('permission:users.delete');
});
```

---

## 5️⃣ Hiển Thị/Ẩn UI Trong Blade

### Ví Dụ 1: Danh Sách Diplomas

```blade
{{-- resources/views/diplomas/index.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Danh Sách Văn Bằng</h1>

        {{-- Chỉ hiển thị nút Cấp mới nếu có quyền --}}
        @can('diplomas.create')
            <a href="{{ route('diplomas.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Cấp văn bằng mới
            </a>
        @endcan
    </div>

    {{-- Chỉ hiển thị nút Export nếu có quyền --}}
    @can('diplomas.export')
        <div class="mb-3">
            <a href="{{ route('diplomas.export') }}" class="btn btn-success">
                <i class="fas fa-file-export"></i> Xuất Excel
            </a>
        </div>
    @endcan

    <table class="table">
        <thead>
            <tr>
                <th>Mã sinh viên</th>
                <th>Họ tên</th>
                <th>Loại văn bằng</th>
                <th>Ngày cấp</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach($diplomas as $diploma)
                <tr>
                    <td>{{ $diploma->student->student_code }}</td>
                    <td>{{ $diploma->student->full_name }}</td>
                    <td>{{ $diploma->diplomaBlank->diplomaBlankType->type_name }}</td>
                    <td>{{ $diploma->issue_date->format('d/m/Y') }}</td>
                    <td>
                        {{-- Nút Sửa: chỉ hiển thị nếu có quyền edit --}}
                        @can('diplomas.edit')
                            <a href="{{ route('diplomas.edit', $diploma->id) }}"
                               class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> Sửa
                            </a>
                        @endcan

                        {{-- Nút Xóa: CHỈ Admin --}}
                        @admin
                            <form action="{{ route('diplomas.destroy', $diploma->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Bạn có chắc muốn xóa?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </form>
                        @endadmin

                        {{-- Hiển thị message cho user không có quyền --}}
                        @cannot('diplomas.edit')
                            <span class="text-muted">Không có quyền sửa</span>
                        @endcannot
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $diplomas->links() }}
</div>
@endsection
```

### Ví Dụ 2: Navbar với Quyền

```blade
{{-- resources/views/layouts/navbar.blade.php --}}

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">Quản Lý Văn Bằng</a>

        <ul class="navbar-nav">
            {{-- Link Phôi: chỉ hiển thị nếu có quyền xem --}}
            @can('diploma-blanks.view')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('diploma-blanks.index') }}">
                        <i class="fas fa-file"></i> Quản lý phôi
                    </a>
                </li>
            @endcan

            {{-- Link Văn bằng: chỉ hiển thị nếu có quyền xem --}}
            @can('diplomas.view')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('diplomas.index') }}">
                        <i class="fas fa-graduation-cap"></i> Văn bằng
                    </a>
                </li>
            @endcan

            {{-- Link Chứng chỉ: chỉ hiển thị nếu có quyền xem --}}
            @can('certificates.view')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('certificates.index') }}">
                        <i class="fas fa-certificate"></i> Chứng chỉ
                    </a>
                </li>
            @endcan

            {{-- Link Users: CHỈ Admin --}}
            @admin
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('users.index') }}">
                        <i class="fas fa-users"></i> Quản lý người dùng
                    </a>
                </li>
            @endadmin

            {{-- Link Settings: CHỈ Admin --}}
            @can('settings.view')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('settings.index') }}">
                        <i class="fas fa-cog"></i> Cài đặt
                    </a>
                </li>
            @endcan

            {{-- Profile: Tất cả users --}}
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                    {{ auth()->user()->full_name }}
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user"></i> Thông tin cá nhân
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.change-password') }}">
                            <i class="fas fa-key"></i> Đổi mật khẩu
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
```

### Ví Dụ 3: Dashboard với Thống Kê Theo Quyền

```blade
{{-- resources/views/dashboard.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Dashboard</h1>

    <div class="row">
        {{-- Card Phôi: chỉ hiển thị nếu có quyền --}}
        @can('diploma-blanks.view')
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5>Phôi Văn Bằng</h5>
                        <h2>{{ $diplomaBlanksCount ?? 0 }}</h2>
                        <a href="{{ route('diploma-blanks.index') }}" class="btn btn-light btn-sm">
                            Xem chi tiết →
                        </a>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Card Văn bằng: chỉ hiển thị nếu có quyền --}}
        @can('diplomas.view')
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5>Văn Bằng Đã Cấp</h5>
                        <h2>{{ $diplomasCount ?? 0 }}</h2>
                        <a href="{{ route('diplomas.index') }}" class="btn btn-light btn-sm">
                            Xem chi tiết →
                        </a>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Card Users: CHỈ Admin --}}
        @admin
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5>Người Dùng</h5>
                        <h2>{{ $usersCount ?? 0 }}</h2>
                        <a href="{{ route('users.index') }}" class="btn btn-light btn-sm">
                            Quản lý →
                        </a>
                    </div>
                </div>
            </div>
        @endadmin
    </div>

    {{-- Hiển thị thông báo nếu user là Viewer --}}
    @hasRole('Tra cứu')
        <div class="alert alert-info mt-4">
            <i class="fas fa-info-circle"></i>
            Bạn đang đăng nhập với quyền <strong>Tra cứu</strong>.
            Bạn chỉ có thể xem thông tin, không thể thêm/sửa/xóa.
        </div>
    @endhasRole
</div>
@endsection
```

---

## 6️⃣ Xử Lý Access Denied (403)

```blade
{{-- resources/views/error.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container text-center">
    <div class="error-page">
        <h1 class="display-1">{{ $status }}</h1>

        @if($status == 403)
            <h2>Truy Cập Bị Từ Chối</h2>
            <p class="lead">{{ $message }}</p>

            <div class="alert alert-warning mt-4">
                <i class="fas fa-exclamation-triangle"></i>
                Bạn không có quyền truy cập trang này. Vui lòng liên hệ quản trị viên nếu bạn nghĩ đây là lỗi.
            </div>

            <a href="{{ url()->previous() }}" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-home"></i> Về trang chủ
            </a>
        @else
            <h2>{{ $message }}</h2>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                <i class="fas fa-home"></i> Về trang chủ
            </a>
        @endif
    </div>
</div>
@endsection
```

---

## 7️⃣ Kiểm Tra Trong API

```php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DiplomaApiController extends Controller
{
    public function index(Request $request)
    {
        // Kiểm tra quyền
        if (!$request->user()->hasPermission('diplomas.view')) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xem danh sách văn bằng'
            ], 403);
        }

        $diplomas = Diploma::paginate(20);

        return response()->json([
            'success' => true,
            'data' => $diplomas
        ]);
    }

    public function destroy(Request $request, $id)
    {
        // Chỉ Admin mới xóa được
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ Admin mới có quyền xóa văn bằng'
            ], 403);
        }

        $diploma = Diploma::findOrFail($id);
        $diploma->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa văn bằng thành công'
        ]);
    }
}
```

---

## 🎯 Kết Luận

Với hệ thống phân quyền đã được test kỹ lưỡng, bạn có thể:

1. ✅ **Cấp/Thu hồi quyền** linh hoạt cho từng role
2. ✅ **Kiểm tra quyền** trong Controller với `hasPermission()`, `isAdmin()`
3. ✅ **Bảo vệ routes** với middleware `permission:permission.name`
4. ✅ **Ẩn/Hiện UI** với Blade directives `@can`, `@admin`, `@hasRole`
5. ✅ **Xử lý 403** với error page thân thiện
6. ✅ **API security** với permission check trong API controllers

**Hệ thống sẵn sàng cho production!** 🚀
