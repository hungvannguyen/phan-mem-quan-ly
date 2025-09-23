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
                <h1 class="diploma-page-title">Quản lý Sinh viên và Văn bằng</h1>
                <p class="diploma-page-subtitle">Tìm kiếm và quản lý thông tin sinh viên cùng văn bằng đã cấp</p>
            </div>

            <!-- Search Form -->
            <div class="diploma-search-card">
                <form class="diploma-search-form" method="GET" action="{{ route('diploma-management') }}">
                    <div class="search-form-grid">
                        <div class="form-field">
                            <label for="full_name" class="field-label">Họ và tên sinh viên</label>
                            <input type="text" id="full_name" name="full_name" class="field-input"
                                placeholder="Nhập họ tên sinh viên" value="{{ request('full_name') }}">
                        </div>

                        <div class="form-field">
                            <label for="student_code" class="field-label">Mã số sinh viên</label>
                            <input type="text" id="student_code" name="student_code" class="field-input"
                                placeholder="Nhập mã số sinh viên" value="{{ request('student_code') }}">
                        </div>

                        <div class="form-field">
                            <label for="class_name" class="field-label">Lớp học</label>
                            <input type="text" id="class_name" name="class_name" class="field-input"
                                placeholder="Nhập tên lớp" value="{{ request('class_name') }}">
                        </div>

                        <div class="form-field">
                            <label for="date_of_birth" class="field-label">Ngày sinh</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" class="field-input"
                                value="{{ request('date_of_birth') }}">
                        </div>

                        <div class="form-field">
                            <label for="major_id" class="field-label">Ngành đào tạo</label>
                            <select id="major_id" name="major_id" class="field-select">
                                <option value="">-- Tất cả ngành --</option>
                                @if (isset($majors))
                                    @foreach ($majors as $major)
                                        <option value="{{ $major->major_id }}"
                                            {{ request('major_id') == $major->major_id ? 'selected' : '' }}>
                                            {{ $major->major_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="form-field">
                            <label for="degree_type" class="field-label">Loại văn bằng</label>
                            <select id="degree_type" name="degree_type" class="field-select">
                                <option value="">-- Tất cả loại văn bằng --</option>
                                <option value="bachelor" {{ request('degree_type') == 'bachelor' ? 'selected' : '' }}>
                                    Cử nhân
                                </option>
                                <option value="master" {{ request('degree_type') == 'master' ? 'selected' : '' }}>
                                    Thạc sĩ
                                </option>
                                <option value="doctor" {{ request('degree_type') == 'doctor' ? 'selected' : '' }}>
                                    Tiến sĩ
                                </option>
                                <option value="certificate"
                                    {{ request('degree_type') == 'certificate' ? 'selected' : '' }}>
                                    Chứng chỉ
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Search Actions -->
                    <div class="search-actions">
                        <button type="submit" class="btn-search">
                            <i class="fas fa-search"></i>
                            Tìm kiếm
                        </button>
                        <a href="{{ route('diploma-management') }}" class="btn-reset">
                            <i class="fas fa-times"></i>
                            Xóa bộ lọc
                        </a>
                    </div>
                </form>
            </div>

            <!-- Action Buttons -->
            <div class="diploma-actions">
                <a href="{{ route('student.create') }}" class="action-btn action-btn-primary">
                    Thêm sinh viên mới
                </a>
                <button type="button" class="action-btn action-btn-warning">
                    Cấp văn bằng
                </button>
                <button type="button" class="action-btn action-btn-info">
                    In danh sách
                </button>
                <button type="button" class="action-btn action-btn-success">
                    Xuất Excel
                </button>
            </div>
        </div>
        <div class="table-section">
            <div class="table-wrapper" id="table-data">
                @include('components.students.table')
            </div>
        </div>
    </main>
@endsection
