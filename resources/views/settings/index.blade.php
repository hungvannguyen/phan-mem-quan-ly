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
                    <h1 class="page-title">Quản lý Danh mục</h1>
                    <p class="page-subtitle">Quản lý các danh mục hệ thống: loại văn bằng, chứng chỉ và ngành đào tạo</p>
                </div>
            </div>

            <!-- Settings Sections -->
            <div class="settings-sections">
                <!-- Diploma Blank Types Section -->
                <div class="settings-section">
                    <div class="section-header">
                        <div class="section-title">
                            <h2>
                                <i class="fas fa-certificate"></i>
                                Loại văn bằng và chứng chỉ
                            </h2>
                            <p>Quản lý các loại văn bằng và chứng chỉ trong hệ thống</p>
                        </div>
                        <div class="section-actions">
                            <a href="{{ route('settings.types.create') }}" class="btn-primary">
                                <i class="fas fa-plus"></i>
                                Thêm loại mới
                            </a>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="recalled-list-statistics">
                        <div class="stat-card stat-total">
                            <div class="stat-icon">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ number_format($types->total()) }}</h3>
                                <p>Tổng số loại</p>
                            </div>
                        </div>

                        <div class="stat-card stat-active">
                            <div class="stat-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ number_format($types->filter(fn($t) => str_contains(strtolower($t->type_name), 'bằng'))->count()) }}
                                </h3>
                                <p>Văn bằng</p>
                            </div>
                        </div>

                        <div class="stat-card stat-inactive">
                            <div class="stat-icon">
                                <i class="fas fa-award"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ number_format($types->filter(fn($t) => str_contains(strtolower($t->type_name), 'chứng chỉ'))->count()) }}
                                </h3>
                                <p>Chứng chỉ</p>
                            </div>
                        </div>

                        <div class="stat-card stat-new">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ number_format($types->where('created_at', '>=', now()->startOfMonth())->count()) }}
                                </h3>
                                <p>Mới trong tháng</p>
                            </div>
                        </div>
                    </div>

                    <!-- Search Form -->
                    <div class="search-card">
                        <form class="search-form" method="GET" action="{{ route('settings.index') }}">
                            <div class="form-grid">
                                <div class="form-field">
                                    <label for="search_type" class="field-label">Tìm kiếm</label>
                                    <div class="search-input-wrapper">
                                        <input type="text" id="search_type" name="search_type" class="field-input"
                                            placeholder="Tìm kiếm theo tên loại hoặc mã tiền tố..."
                                            value="{{ request('search_type') }}">
                                        @if (request('search_type'))
                                            <button type="button" class="clear-search" onclick="clearSearchType()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-field">
                                    <label for="sort_by_type" class="field-label">Sắp xếp theo</label>
                                    <select name="sort_by_type" id="sort_by_type" class="field-select">
                                        <option value="created_at"
                                            {{ request('sort_by_type') === 'created_at' ? 'selected' : '' }}>
                                            Ngày tạo</option>
                                        <option value="type_name"
                                            {{ request('sort_by_type') === 'type_name' ? 'selected' : '' }}>
                                            Tên loại</option>
                                        <option value="prefix"
                                            {{ request('sort_by_type') === 'prefix' ? 'selected' : '' }}>
                                            Mã tiền tố</option>
                                    </select>
                                </div>

                                <div class="form-field">
                                    <label for="sort_order_type" class="field-label">Thứ tự</label>
                                    <select name="sort_order_type" id="sort_order_type" class="field-select">
                                        <option value="desc"
                                            {{ request('sort_order_type') === 'desc' ? 'selected' : '' }}>
                                            Giảm dần
                                        </option>
                                        <option value="asc"
                                            {{ request('sort_order_type') === 'asc' ? 'selected' : '' }}>Tăng
                                            dần
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-search"></i>
                                    Tìm kiếm
                                </button>
                                @if (request()->hasAny(['search_type', 'sort_by_type', 'sort_order_type']))
                                    <a href="{{ route('settings.index') }}" class="btn-secondary">
                                        <i class="fas fa-redo"></i>
                                        Đặt lại
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <!-- Results Info -->
                    @if (request('search_type'))
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Tìm thấy <strong>{{ $types->total() }}</strong> kết quả cho từ khóa
                            "<strong>{{ request('search_type') }}</strong>"
                            @if ($types->total() === 0)
                                <div class="mt-2">
                                    <a href="{{ route('settings.index') }}" class="text-blue-600 hover:text-blue-800">
                                        Xóa bộ lọc và xem tất cả loại văn bằng
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Types Table -->
                    <div class="table-section">
                        @include('settings.types.table')
                    </div>
                </div>

                <!-- Majors Section -->
                <div class="settings-section">
                    <div class="section-header">
                        <div class="section-title">
                            <h2>
                                <i class="fas fa-book"></i>
                                Ngành đào tạo
                            </h2>
                            <p>Quản lý các ngành đào tạo trong hệ thống</p>
                        </div>
                        <div class="section-actions">
                            <a href="{{ route('settings.majors.create') }}" class="btn-primary">
                                <i class="fas fa-plus"></i>
                                Thêm ngành mới
                            </a>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="recalled-list-statistics">
                        <div class="stat-card stat-total">
                            <div class="stat-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ number_format($majors->total()) }}</h3>
                                <p>Tổng số ngành</p>
                            </div>
                        </div>

                        <div class="stat-card stat-active">
                            <div class="stat-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ number_format($majors->sum(fn($m) => $m->students()->count())) }}</h3>
                                <p>Tổng sinh viên</p>
                            </div>
                        </div>

                        <div class="stat-card stat-inactive">
                            <div class="stat-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ $majors->count() > 0 ? number_format($majors->sum(fn($m) => $m->students()->count()) / $majors->count(), 1) : 0 }}
                                </h3>
                                <p>TB sinh viên/ngành</p>
                            </div>
                        </div>

                        <div class="stat-card stat-new">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ number_format($majors->where('created_at', '>=', now()->startOfMonth())->count()) }}
                                </h3>
                                <p>Mới trong tháng</p>
                            </div>
                        </div>
                    </div>

                    <!-- Search Form -->
                    <div class="search-card">
                        <form class="search-form" method="GET" action="{{ route('settings.index') }}">
                            <div class="form-grid">
                                <div class="form-field">
                                    <label for="search_major" class="field-label">Tìm kiếm</label>
                                    <div class="search-input-wrapper">
                                        <input type="text" id="search_major" name="search_major" class="field-input"
                                            placeholder="Tìm kiếm theo tên ngành hoặc mã ngành..."
                                            value="{{ request('search_major') }}">
                                        @if (request('search_major'))
                                            <button type="button" class="clear-search" onclick="clearSearchMajor()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-field">
                                    <label for="sort_by_major" class="field-label">Sắp xếp theo</label>
                                    <select name="sort_by_major" id="sort_by_major" class="field-select">
                                        <option value="created_at"
                                            {{ request('sort_by_major') === 'created_at' ? 'selected' : '' }}>
                                            Ngày tạo</option>
                                        <option value="major_name"
                                            {{ request('sort_by_major') === 'major_name' ? 'selected' : '' }}>
                                            Tên ngành</option>
                                        <option value="major_code"
                                            {{ request('sort_by_major') === 'major_code' ? 'selected' : '' }}>
                                            Mã ngành</option>
                                    </select>
                                </div>

                                <div class="form-field">
                                    <label for="sort_order_major" class="field-label">Thứ tự</label>
                                    <select name="sort_order_major" id="sort_order_major" class="field-select">
                                        <option value="desc"
                                            {{ request('sort_order_major') === 'desc' ? 'selected' : '' }}>
                                            Giảm dần
                                        </option>
                                        <option value="asc"
                                            {{ request('sort_order_major') === 'asc' ? 'selected' : '' }}>Tăng
                                            dần
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-search"></i>
                                    Tìm kiếm
                                </button>
                                @if (request()->hasAny(['search_major', 'sort_by_major', 'sort_order_major']))
                                    <a href="{{ route('settings.index') }}" class="btn-secondary">
                                        <i class="fas fa-redo"></i>
                                        Đặt lại
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <!-- Results Info -->
                    @if (request('search_major'))
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Tìm thấy <strong>{{ $majors->total() }}</strong> kết quả cho từ khóa
                            "<strong>{{ request('search_major') }}</strong>"
                            @if ($majors->total() === 0)
                                <div class="mt-2">
                                    <a href="{{ route('settings.index') }}" class="text-blue-600 hover:text-blue-800">
                                        Xóa bộ lọc và xem tất cả ngành đào tạo
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Majors Table -->
                    <div class="table-section">
                        @include('settings.majors.table')
                    </div>
                </div>
            </div>
        </div>
    </main>

    <style>
        .settings-sections {
            margin-top: 2rem;
        }

        .settings-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .section-title h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title h2 i {
            color: #3b82f6;
        }

        .section-title p {
            color: #6b7280;
            font-size: 0.95rem;
            margin: 0;
        }

        .section-actions {
            display: flex;
            gap: 1rem;
        }
    </style>

    <script>
        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                setTimeout(() => bsAlert.close(), 5000);
            });
        }, 100);

        // Clear search function for types
        function clearSearchType() {
            const searchInput = document.getElementById('search_type');
            searchInput.value = '';
            searchInput.form.submit();
        }

        // Clear search function for majors
        function clearSearchMajor() {
            const searchInput = document.getElementById('search_major');
            searchInput.value = '';
            searchInput.form.submit();
        }
    </script>
@endsection
