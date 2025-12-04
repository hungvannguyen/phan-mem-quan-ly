@extends('layouts.default')

@section('content')
    <main class="management-page">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="form-section">
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <h1 class="page-title">Quản lý Permissions</h1>
                    <p class="page-subtitle">Quản lý các quyền truy cập và phân quyền trong hệ thống</p>
                </div>
                <div class="header-actions">
                    <a href="{{ route('permissions.create') }}" class="btn-primary">
                        <i class="fas fa-plus"></i>
                        Thêm Permission
                    </a>
                    <a href="{{ route('user-management') }}" class="btn-secondary ms-2">
                        <i class="fas fa-users"></i>
                        Quản lý Người Dùng
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="recalled-list-statistics">
                <div class="stat-card stat-total">
                    <div class="stat-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ number_format($permissions->total()) }}</h3>
                        <p>Tổng số permissions</p>
                    </div>
                </div>

                <div class="stat-card stat-active">
                    <div class="stat-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ number_format($categories->count()) }}</h3>
                        <p>Danh mục</p>
                    </div>
                </div>

                <div class="stat-card stat-inactive">
                    <div class="stat-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ number_format(\App\Models\Role::count()) }}</h3>
                        <p>Roles trong hệ thống</p>
                    </div>
                </div>

                <div class="stat-card stat-new">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ number_format($permissions->where('created_at', '>=', now()->startOfMonth())->count()) }}
                        </h3>
                        <p>Mới trong tháng</p>
                    </div>
                </div>
            </div>

            <!-- Search Form -->
            <div class="search-card">
                <form class="search-form" method="GET" action="{{ route('permissions.index') }}">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="search" class="field-label">Tìm kiếm</label>
                            <div class="search-input-wrapper">
                                <input type="text" id="search" name="search" class="field-input"
                                    placeholder="Tìm theo tên, hiển thị, danh mục..." value="{{ request('search') }}">
                                @if (request('search'))
                                    <button type="button" class="clear-search" onclick="clearSearch()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="category" class="field-label">Danh mục</label>
                            <select name="category" id="category" class="field-select">
                                <option value="">Tất cả danh mục</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}"
                                        {{ request('category') == $cat ? 'selected' : '' }}>
                                        {{ ucfirst($cat) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-field">
                            <label for="sort_by" class="field-label">Sắp xếp theo</label>
                            <select name="sort_by" id="sort_by" class="field-select">
                                <option value="category" {{ request('sort_by') === 'category' ? 'selected' : '' }}>
                                    Danh mục</option>
                                <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Tên permission
                                </option>
                                <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>
                                    Ngày tạo</option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label for="sort_order" class="field-label">Thứ tự</label>
                            <select name="sort_order" id="sort_order" class="field-select">
                                <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Tăng dần
                                </option>
                                <option value="desc" {{ request('sort_order', 'asc') === 'desc' ? 'selected' : '' }}>Giảm
                                    dần</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-search"></i>
                            Tìm kiếm
                        </button>
                        @if (request()->hasAny(['search', 'category', 'sort_by', 'sort_order']))
                            <a href="{{ route('permissions.index') }}" class="btn-secondary">
                                <i class="fas fa-redo"></i>
                                Đặt lại
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Results Info -->
            @if (request('search'))
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Tìm thấy <strong>{{ $permissions->total() }}</strong> kết quả cho từ khóa
                    "<strong>{{ request('search') }}</strong>"
                    @if ($permissions->total() === 0)
                        <div class="mt-2">
                            <a href="{{ route('permissions.index') }}" class="text-blue-600 hover:text-blue-800">
                                Xóa bộ lọc và xem tất cả permissions
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Permissions Table -->
            <div class="table-section">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th width="5%">STT</th>
                                <th width="20%">Tên Permission</th>
                                <th width="18%">Tên Hiển Thị</th>
                                <th width="12%">Danh Mục</th>
                                <th width="25%">Mô Tả</th>
                                <th width="10%">Roles</th>
                                <th width="10%">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permissions as $index => $permission)
                                <tr>
                                    <td class="text-center">{{ $permissions->firstItem() + $index }}</td>
                                    <td>
                                        <code class="text-blue-600">{{ $permission->name }}</code>
                                    </td>
                                    <td>{{ $permission->display_name }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($permission->category) }}</span>
                                    </td>
                                    <td>
                                        <small class="text-gray-600">{{ $permission->description ?? '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">{{ $permission->roles()->count() }}</span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('permissions.edit', $permission->permission_id) }}"
                                                class="btn-action btn-edit" title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('permissions.destroy', $permission->permission_id) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Bạn có chắc muốn xóa permission này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action btn-delete" title="Xóa"
                                                    {{ $permission->roles()->count() > 0 ? 'disabled' : '' }}>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-4 text-center">
                                        <div class="empty-state">
                                            <i class="fas fa-shield-alt fa-3x mb-3 text-gray-300"></i>
                                            <p class="text-gray-500">Không tìm thấy permission nào</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($permissions->hasPages())
                    <div class="pagination-wrapper">
                        <div class="pagination-info">
                            Hiển thị {{ $permissions->firstItem() ?? 0 }} - {{ $permissions->lastItem() ?? 0 }}
                            trong tổng số {{ $permissions->total() }} permissions
                        </div>
                        <div class="pagination-links">
                            {{ $permissions->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <script>
        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                setTimeout(() => bsAlert.close(), 5000);
            });
        }, 100);

        // Clear search function
        function clearSearch() {
            const searchInput = document.getElementById('search');
            searchInput.value = '';
            searchInput.form.submit();
        }
    </script>
@endsection
