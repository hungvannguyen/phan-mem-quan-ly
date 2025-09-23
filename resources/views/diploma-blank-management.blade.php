@extends('layouts.default')

@section('content')
    <main class="diploma-management">
        {{-- Hiển thị thông báo --}}
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

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="diploma-form-section">
            <!-- Page Header -->
            <div class="diploma-page-header">
                <h1 class="diploma-page-title">Quản lý Phôi Văn bằng</h1>
                <p class="diploma-page-subtitle">Tìm kiếm và quản lý thông tin phôi văn bằng trong hệ thống</p>
            </div>

            <!-- Search Form -->
            <div class="diploma-search-card">
                <form class="diploma-search-form" method="GET" action="{{ route('diploma-blank-management') }}">
                    <div class="search-form-grid">
                        <div class="form-field">
                            <label for="serial_number" class="field-label">Số seri phôi</label>
                            <input type="text" id="serial_number" name="serial_number" class="field-input"
                                placeholder="Nhập số seri phôi" value="{{ request('serial_number') }}">
                        </div>

                        <div class="form-field">
                            <label for="type_id" class="field-label">Loại phôi văn bằng</label>
                            <select id="type_id" name="type_id" class="field-select">
                                <option value="">-- Tất cả loại phôi --</option>
                                @if (isset($diplomaBlankTypes))
                                    @foreach ($diplomaBlankTypes as $type)
                                        <option value="{{ $type->type_id }}"
                                            {{ request('type_id') == $type->type_id ? 'selected' : '' }}>
                                            {{ $type->type_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="form-field">
                            <label for="status" class="field-label">Trạng thái phôi</label>
                            <select id="status" name="status" class="field-select">
                                <option value="">-- Tất cả trạng thái --</option>
                                <option value="InStock" {{ request('status') == 'InStock' ? 'selected' : '' }}>
                                    Trong kho
                                </option>
                                <option value="Issued" {{ request('status') == 'Issued' ? 'selected' : '' }}>
                                    Đã cấp
                                </option>
                                <option value="Damaged" {{ request('status') == 'Damaged' ? 'selected' : '' }}>
                                    Hư hỏng
                                </option>
                                <option value="Recalled" {{ request('status') == 'Recalled' ? 'selected' : '' }}>
                                    Thu hồi
                                </option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label for="import_date_from" class="field-label">Ngày nhập từ</label>
                            <input type="date" id="import_date_from" name="import_date_from" class="field-input"
                                value="{{ request('import_date_from') }}">
                        </div>

                        <div class="form-field">
                            <label for="import_date_to" class="field-label">Ngày nhập đến</label>
                            <input type="date" id="import_date_to" name="import_date_to" class="field-input"
                                value="{{ request('import_date_to') }}">
                        </div>

                        <div class="form-field">
                            <label for="issue_date_from" class="field-label">Ngày cấp từ</label>
                            <input type="date" id="issue_date_from" name="issue_date_from" class="field-input"
                                value="{{ request('issue_date_from') }}">
                        </div>
                    </div>

                    <!-- Search Actions -->
                    <div class="search-actions">
                        <button type="submit" class="btn-search">
                            <i class="fas fa-search"></i>
                            Tìm kiếm
                        </button>
                        <a href="{{ route('diploma-blank-management') }}" class="btn-reset">
                            <i class="fas fa-times"></i>
                            Xóa bộ lọc
                        </a>
                    </div>
                </form>
            </div>

            <!-- Action Buttons -->
            <div class="diploma-actions">
                <a href="{{ route('diploma-blank.import') }}" class="action-btn action-btn-primary">
                    <i class="fas fa-plus"></i>
                    Nhập phôi mới
                </a>
                <button type="button" class="action-btn action-btn-warning">
                    <i class="fas fa-upload"></i>
                    Nhập từ Excel
                </button>
                <button type="button" class="action-btn action-btn-info">
                    <i class="fas fa-print"></i>
                    In danh sách
                </button>
                <button type="button" class="action-btn action-btn-success">
                    <i class="fas fa-file-excel"></i>
                    Xuất Excel
                </button>
                <button type="button" class="action-btn action-btn-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Báo cáo hư hỏng
                </button>
            </div>
        </div>
        <div class="table-section">
            <div class="table-wrapper" id="table-data">
                @include('components.diploma-blanks.table')
            </div>
        </div>
    </main>
@endsection
