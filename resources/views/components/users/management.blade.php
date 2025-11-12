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
                    <h1 class="page-title">Quản lý Người Dùng</h1>
                    <p class="page-subtitle">Quản lý tài khoản người dùng, phân quyền và theo dõi hoạt động trong hệ thống
                    </p>
                </div>
                <div class="header-actions">
                    <a href="{{ route('user.create') }}" class="btn-primary">
                        <i class="fas fa-plus"></i>
                        Thêm người dùng mới
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="recalled-list-statistics">
                <div class="stat-card stat-total">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ number_format($users->total()) }}</h3>
                        <p>Tổng số người dùng</p>
                    </div>
                </div>

                <div class="stat-card stat-active">
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ number_format($users->where('is_active', true)->count()) }}</h3>
                        <p>Đang hoạt động</p>
                    </div>
                </div>

                <div class="stat-card stat-inactive">
                    <div class="stat-icon">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ number_format($users->where('is_active', false)->count()) }}</h3>
                        <p>Vô hiệu hóa</p>
                    </div>
                </div>

                <div class="stat-card stat-new">
                    <div class="stat-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ number_format($users->where('created_at', '>=', now()->startOfMonth())->count()) }}</h3>
                        <p>Mới trong tháng</p>
                    </div>
                </div>
            </div>

            <!-- Search Form -->
            <div class="search-card">
                <form class="search-form" method="GET" action="{{ route('user-management') }}">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="search" class="field-label">Tìm kiếm</label>
                            <div class="search-input-wrapper">
                                <input type="text" id="search" name="search" class="field-input"
                                    placeholder="Tìm kiếm theo tên đăng nhập, họ tên hoặc email..."
                                    value="{{ request('search') }}">
                                @if (request('search'))
                                    <button type="button" class="clear-search" onclick="clearSearch()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="status" class="field-label">Trạng thái</label>
                            <select name="status" id="status" class="field-select">
                                <option value="">Tất cả trạng thái</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Vô hiệu hóa
                                </option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label for="sort_by" class="field-label">Sắp xếp theo</label>
                            <select name="sort_by" id="sort_by" class="field-select">
                                <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>
                                    Ngày tạo</option>
                                <option value="updated_at" {{ request('sort_by') === 'updated_at' ? 'selected' : '' }}>
                                    Ngày cập nhật</option>
                                <option value="username" {{ request('sort_by') === 'username' ? 'selected' : '' }}>Tên
                                    đăng nhập</option>
                                <option value="full_name" {{ request('sort_by') === 'full_name' ? 'selected' : '' }}>Họ
                                    tên</option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label for="sort_order" class="field-label">Thứ tự</label>
                            <select name="sort_order" id="sort_order" class="field-select">
                                <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>Giảm dần
                                </option>
                                <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Tăng dần
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-search"></i>
                            Tìm kiếm
                        </button>
                        @if (request()->hasAny(['search', 'status', 'sort_by', 'sort_order']))
                            <a href="{{ route('user-management') }}" class="btn-secondary">
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
                    Tìm thấy <strong>{{ $users->total() }}</strong> kết quả cho từ khóa
                    "<strong>{{ request('search') }}</strong>"
                    @if ($users->total() === 0)
                        <div class="mt-2">
                            <a href="{{ route('user-management') }}" class="text-blue-600 hover:text-blue-800">
                                Xóa bộ lọc và xem tất cả người dùng
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <!-- User Table -->
            <div class="table-section">
                @include('components.users.table')
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
