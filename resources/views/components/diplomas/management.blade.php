@extends('layouts.default')

@section('content')
    <style>
        .suggestion-item.selected {
            background-color: #eff6ff;
            border-left: 3px solid #3b82f6;
        }

        .flexible-date-search .field-input:focus+.date-search-examples {
            color: #3b82f6;
        }

        .btn-search {
            transition: all 0.2s ease;
        }

        .btn-search-disabled {
            background-color: #e5e7eb !important;
            color: #9ca3af !important;
            cursor: not-allowed !important;
            border-color: #d1d5db !important;
        }

        .btn-search-enabled {
            background-color: #3b82f6 !important;
            color: white !important;
            cursor: pointer !important;
            border-color: #3b82f6 !important;
        }

        .btn-search-enabled:hover {
            background-color: #1e40af;
            transform: translateY(-1px);
        }

        /* Input validation styles */
        .border-red-500 {
            border-color: #ef4444 !important;
        }

        .bg-red-50 {
            background-color: #fef2f2 !important;
        }

        .flexible-date-input.border-red-500:focus {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        /* Error message styles */
        .input-error-message {
            margin-top: 4px;
        }

        .text-error {
            color: #ef4444;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .text-error i {
            font-size: 11px;
        }

        .input-error-message.hidden {
            display: none;
        }
    </style>
    </style>
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

                        <x-flexible-date-search id="date_of_birth" name="date_of_birth" label="Ngày sinh"
                            placeholder="Nhập ngày sinh (VD: 15, 03/1995, 15/03/1995)"
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
                            <label for="diploma_blank_type_id" class="field-label">Loại văn bằng</label>
                            <select id="diploma_blank_type_id" name="diploma_blank_type_id" class="field-select">
                                <option value="">-- Tất cả loại văn bằng --</option>
                                @if (isset($diplomaBlankTypes))
                                    @foreach ($diplomaBlankTypes as $type)
                                        <option value="{{ $type->type_id }}"
                                            {{ request('diploma_blank_type_id') == $type->type_id ? 'selected' : '' }}>
                                            {{ $type->type_name }}
                                        </option>
                                    @endforeach
                                @endif
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
                {{-- <button type="button" class="action-btn action-btn-info">
                    In danh sách
                </button>
                <button type="button" class="action-btn action-btn-success">
                    Xuất Excel
                </button> --}}
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
        // Helper function for flexible date search
        function getValidationErrorMessage(input) {
            if (!input || input.trim() === '') return '';

            const trimmed = input.trim();
            const currentYear = new Date().getFullYear();

            // Check if it's a display text format (already validated)
            if (trimmed.startsWith('Ngày ') || trimmed.startsWith('Tháng ') || trimmed.startsWith('Năm ')) {
                return '';
            }

            // Check if it's already a backend format (already processed)
            if (trimmed.includes(':')) {
                return '';
            }

            // Validate single number (day or month or year)
            if (/^\d{1,2}$/.test(trimmed)) {
                const num = parseInt(trimmed);
                if (num < 1 || num > 31) {
                    return 'Số phải từ 1-31';
                }
                return '';
            }

            // Validate year format (YYYY)
            if (/^\d{4}$/.test(trimmed)) {
                const year = parseInt(trimmed);
                if (year < 1900 || year > currentYear + 10) {
                    return `Năm phải từ 1900-${currentYear + 10}`;
                }
                return '';
            }

            // Validate month/year format (MM/YYYY)
            if (/^\d{1,2}\/\d{4}$/.test(trimmed)) {
                const [month, year] = trimmed.split('/').map(n => parseInt(n));
                if (month < 1 || month > 12) {
                    return 'Tháng phải từ 1-12';
                }
                if (year < 1900 || year > currentYear + 10) {
                    return `Năm phải từ 1900-${currentYear + 10}`;
                }
                return '';
            }

            // Validate day/month format (DD/MM)
            if (/^\d{1,2}\/\d{1,2}$/.test(trimmed)) {
                const [day, month] = trimmed.split('/').map(n => parseInt(n));
                if (day < 1 || day > 31) {
                    return 'Ngày phải từ 1-31';
                }
                if (month < 1 || month > 12) {
                    return 'Tháng phải từ 1-12';
                }
                return '';
            }

            // Validate full date format (DD/MM/YYYY)
            if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(trimmed)) {
                const [day, month, year] = trimmed.split('/').map(n => parseInt(n));
                if (day < 1 || day > 31) {
                    return 'Ngày phải từ 1-31';
                }
                if (month < 1 || month > 12) {
                    return 'Tháng phải từ 1-12';
                }
                if (year < 1900 || year > currentYear + 10) {
                    return `Năm phải từ 1900-${currentYear + 10}`;
                }
                return '';
            }

            return 'Định dạng không hợp lệ. Ví dụ: 15, 2024, 03/2024, 15/03, 15/03/2024';
        }

        function isValidDateInput(input) {
            if (!input || input.trim() === '') return true; // Empty is valid (no search criteria)

            const trimmed = input.trim();
            const currentYear = new Date().getFullYear();

            // Check if it's a display text format (already validated)
            if (trimmed.startsWith('Ngày ') || trimmed.startsWith('Tháng ') || trimmed.startsWith('Năm ')) {
                return true;
            }

            // Check if it's already a backend format (already processed)
            if (trimmed.includes(':')) {
                return true;
            }

            // Use the error message function for validation
            return getValidationErrorMessage(input) === '';
        }

        function convertDisplayTextToBackendFormat(displayText) {
            // Convert display text like "Tháng 3" to backend format "thang:3"
            if (displayText.startsWith('Ngày ')) {
                const day = displayText.replace('Ngày ', '');
                return `ngay:${day}`;
            } else if (displayText.startsWith('Tháng ')) {
                const monthPart = displayText.replace('Tháng ', '');
                if (monthPart.includes('/')) {
                    return `thang_nam:${monthPart}`;
                } else {
                    return `thang:${monthPart}`;
                }
            } else if (displayText.startsWith('Năm ')) {
                const year = displayText.replace('Năm ', '');
                return `nam:${year}`;
            } else if (/^\d{1,2}\/\d{1,2}$/.test(displayText)) {
                return `ngay_thang:${displayText}`;
            } else if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(displayText)) {
                return `ngay_cu_the:${displayText}`;
            }

            // If no pattern matches, return as is
            return displayText;
        }

        function generateFlexibleDateSuggestions(query) {
            const suggestions = [];
            const currentYear = new Date().getFullYear();

            if (!query || query.length === 0) return suggestions;

            const normalizedQuery = query.toLowerCase().trim();

            // Don't generate suggestions for invalid input
            if (!isValidDateInput(normalizedQuery)) {
                return suggestions;
            }

            if (/^\d{1,2}$/.test(normalizedQuery)) {
                const num = parseInt(normalizedQuery);
                if (num >= 1 && num <= 31) {
                    suggestions.push({
                        type: 'Ngày',
                        text: `Ngày ${num}`,
                        value: `ngay:${num}`,
                        query: normalizedQuery
                    });
                }
                if (num >= 1 && num <= 12) {
                    suggestions.push({
                        type: 'Tháng',
                        text: `Tháng ${num}`,
                        value: `thang:${num}`,
                        query: normalizedQuery
                    });
                }
            } else if (/^\d{4}$/.test(normalizedQuery)) {
                const year = parseInt(normalizedQuery);
                if (year >= 1900 && year <= currentYear + 10) {
                    suggestions.push({
                        type: 'Năm',
                        text: `Năm ${year}`,
                        value: `nam:${year}`,
                        query: normalizedQuery
                    });
                }
            } else if (/^\d{1,2}\/\d{4}$/.test(normalizedQuery)) {
                const [month, year] = normalizedQuery.split('/').map(n => parseInt(n));
                if (month >= 1 && month <= 12 && year >= 1900 && year <= currentYear + 10) {
                    suggestions.push({
                        type: 'Tháng/Năm',
                        text: `Tháng ${month}/${year}`,
                        value: `thang_nam:${month}/${year}`,
                        query: normalizedQuery
                    });
                }
            } else if (/^\d{1,2}\/\d{1,2}$/.test(normalizedQuery)) {
                const [day, month] = normalizedQuery.split('/').map(n => parseInt(n));
                if (day >= 1 && day <= 31 && month >= 1 && month <= 12) {
                    suggestions.push({
                        type: 'Ngày/Tháng',
                        text: `${day}/${month}`,
                        value: `ngay_thang:${day}/${month}`,
                        query: normalizedQuery
                    });
                }
            } else if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(normalizedQuery)) {
                const [day, month, year] = normalizedQuery.split('/').map(n => parseInt(n));
                if (day >= 1 && day <= 31 && month >= 1 && month <= 12 && year >= 1900 && year <= currentYear + 10) {
                    suggestions.push({
                        type: 'Ngày cụ thể',
                        text: `${day}/${month}/${year}`,
                        value: `ngay_cu_the:${day}/${month}/${year}`,
                        query: normalizedQuery
                    });
                }
            }

            return suggestions;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Get form elements
            const searchForm = document.querySelector('.search-form');
            const searchBtn = searchForm.querySelector('button[type="submit"]');
            const formFields = [
                'full_name',
                'student_code',
                'class_name',
                'date_of_birth',
                'major_id',
                'diploma_blank_type_id'
            ];

            // Function to check if form has any data and is valid
            function validateForm() {
                let hasData = false;
                let hasInvalidData = false;

                formFields.forEach(fieldName => {
                    const field = document.querySelector(`[name="${fieldName}"]`);
                    if (field && field.value && field.value.trim() !== '') {
                        hasData = true;

                        // Special validation for date fields
                        if (fieldName === 'date_of_birth') {
                            if (!isValidDateInput(field.value)) {
                                hasInvalidData = true;
                                // Add visual indicator for invalid input
                                field.classList.add('border-red-500', 'bg-red-50');
                            } else {
                                // Remove error indicators
                                field.classList.remove('border-red-500', 'bg-red-50');
                            }
                        }
                    }
                });

                // Enable/disable search button
                if (hasData && !hasInvalidData) {
                    searchBtn.disabled = false;
                    searchBtn.classList.remove('btn-search-disabled');
                    searchBtn.classList.add('btn-search-enabled');
                } else {
                    searchBtn.disabled = true;
                    searchBtn.classList.add('btn-search-disabled');
                    searchBtn.classList.remove('btn-search-enabled');
                }
            }

            // Add event listeners to all form fields
            formFields.forEach(fieldName => {
                const field = document.querySelector(`[name="${fieldName}"]`);
                if (field) {
                    // Handle different input types
                    if (field.type === 'select-one') {
                        field.addEventListener('change', validateForm);
                    } else {
                        field.addEventListener('input', validateForm);
                        field.addEventListener('change', validateForm);
                    }
                }
            });

            // Initial validation on page load
            validateForm();

            // Prevent form submission if no data or invalid data
            searchForm.addEventListener('submit', function(e) {
                if (searchBtn.disabled) {
                    e.preventDefault();
                    alert('Vui lòng nhập ít nhất một tiêu chí tìm kiếm hợp lệ!');
                    return false;
                }

                // Final validation check for date fields
                const dateField = document.querySelector('[name="date_of_birth"]');
                if (dateField && dateField.value.trim()) {
                    const errorMsg = getValidationErrorMessage(dateField.value.trim());
                    if (errorMsg) {
                        e.preventDefault();
                        alert('Lỗi ngày tháng: ' + errorMsg +
                            '\n\nVí dụ hợp lệ:\n- Ngày: 15\n- Năm: 2024\n- Tháng/Năm: 03/2024\n- Ngày/Tháng: 15/03\n- Đầy đủ: 15/03/2024'
                        );
                        return false;
                    }
                }

                // Process flexible date search fields before submission
                const flexibleDateFields = document.querySelectorAll('.flexible-date-input');
                flexibleDateFields.forEach(field => {
                    if (field.dataset.searchValue) {
                        // Use the structured search value instead of display text
                        field.value = field.dataset.searchValue;
                    } else if (field.value.trim()) {
                        // Try to auto-detect format for manual input
                        const suggestions = generateFlexibleDateSuggestions(field.value.trim());
                        if (suggestions.length === 1) {
                            field.value = suggestions[0].value;
                        } else {
                            // Try to convert display text to backend format
                            const backendFormat = convertDisplayTextToBackendFormat(field.value
                                .trim());
                            if (backendFormat !== field.value.trim()) {
                                field.value = backendFormat;
                            }
                        }
                    }
                });
            });
        });

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
