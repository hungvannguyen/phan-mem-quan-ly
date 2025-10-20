@extends('layouts.default')

@section('content')
    <main class="management-page">
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

        <div class="form-section">
            <!-- Page Header -->
            <div class="page">
                <h1 class="page-title">Quản lý Sinh viên và Văn bằng</h1>
                <p class="page-subtitle">Tìm kiếm và quản lý thông tin sinh viên cùng văn bằng đã cấp</p>
            </div>

            <!-- Search Form -->
            <div class="search-card">
                <form class="search-form" method="GET" action="{{ route('diploma-management') }}">
                    <div class="form-grid">
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

                        <x-vietnamese-date-input id="date_of_birth" name="date_of_birth" label="Ngày sinh" :required="false"
                            value="{{ request('date_of_birth') }}" />

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
                            Tìm kiếm
                        </button>
                        <a href="{{ route('diploma-management') }}" class="btn-reset">
                            Xóa bộ lọc
                        </a>
                    </div>
                </form>
            </div>

            <!-- Action Buttons -->
            <div class="page-actions">
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

    <!-- Hidden form for student deletion -->
    <form id="deleteStudentForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function confirmDeleteStudent(studentId, studentName) {
            if (confirm(
                    `Bạn có chắc chắn muốn xóa sinh viên "${studentName}" không?\n\nLưu ý: Việc xóa sinh viên sẽ đồng thời xóa tất cả văn bằng đã cấp và trả lại các phôi văn bằng về kho.`
                )) {
                deleteStudent(studentId);
            }
        }

        function deleteStudent(studentId) {
            const form = document.getElementById('deleteStudentForm');
            form.action = `/student/${studentId}/delete`;
            form.submit();
        }
    </script>
@endsection
