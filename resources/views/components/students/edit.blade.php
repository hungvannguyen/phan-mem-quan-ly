@extends('layouts.default')

@section('content')
    <style>
        .status-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .status-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
        }

        .status-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .status-value {
            font-weight: 600;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-studying {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-graduate {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-dropout {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .status-unknown {
            background-color: #f1f5f9;
            color: #475569;
        }

        .alert-warning,
        .alert-info {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }

        .alert-warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
        }

        .alert-info {
            background-color: #dbeafe;
            border: 1px solid #3b82f6;
            color: #1e40af;
        }

        .field-description {
            margin-top: 0.5rem;
        }

        /* Button Styles */
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #059669;
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background-color: #047857;
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }

        .btn-secondary:hover:not(:disabled) {
            background-color: #4b5563;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Modal Styles */
        #addDegreeModal,
        #editDegreeModal {
            z-index: 9999;
        }

        #addDegreeModal.hidden,
        #editDegreeModal.hidden {
            display: none !important;
        }

        #addDegreeModal:not(.hidden),
        #editDegreeModal:not(.hidden) {
            display: flex !important;
        }

        #addDegreeModal .bg-white,
        #editDegreeModal .bg-white {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* Field Styles for Modal */
        .field-group {
            display: flex;
            flex-direction: column;
        }

        .field-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .field-label.required::after {
            content: ' *';
            color: #ef4444;
        }

        .field-input {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .field-input:focus {
            outline: none;
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }

        /* Degree Header Layout */
        .degree-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }

        .degree-actions {
            flex-shrink: 0;
            display: flex;
            gap: 0.5rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .grid-cols-2 {
                grid-template-columns: 1fr;
            }

            .degree-header {
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
            }
        }

        .date-display {
            margin-top: 0.25rem;
            padding: 0.5rem;
            background-color: #f0fdf4;
            border: 1px solid #22c55e;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            color: #15803d;
            font-weight: 500;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            color: #6b7280;
        }

        input[type="date"]:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
    </style>
    <div class="student-edit-page">
        <!-- Page Header -->
        <div class="page-header">
            <div class="flex w-full items-center justify-between">
                <div>
                    <h1>Chỉnh Sửa Thông Tin Sinh Viên</h1>
                    <p>Mã sinh viên: {{ $student->student_code }}</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="history.back()" class="btn btn-secondary">
                        Quay lại
                    </button>
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('student.update', $student->student_id) }}" class="space-y-8">
            @csrf
            @method('POST')

            <!-- Student Information Section -->
            <div class="section-card">
                <h2 class="section-title">
                    <i class="fas fa-user-edit text-blue-600"></i>
                    Thông tin sinh viên
                </h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div class="form-field">
                        <label for="student_code" class="field-label">Mã sinh viên <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="student_code" name="student_code" class="field-input"
                            value="{{ old('student_code', $student->student_code) }}" required>
                        @error('student_code')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="full_name" class="field-label">Họ và tên <span class="text-red-500">*</span></label>
                        <input type="text" id="full_name" name="full_name" class="field-input"
                            value="{{ old('full_name', $student->full_name) }}" required>
                        @error('full_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <x-vietnamese-date-input id="date_of_birth" name="date_of_birth" label="Ngày sinh" :required="true"
                        value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}"
                        description="Định dạng: ngày/tháng/năm (VD: 15/06/1995)">
                        @if ($student->date_of_birth)
                            <div class="date-display mt-1 text-sm text-green-600">
                                ✓ Ngày sinh hiện tại: {{ $student->date_of_birth->format('d/m/Y') }}
                            </div>
                        @endif
                        @error('date_of_birth')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </x-vietnamese-date-input>

                    <div class="form-field">
                        <label for="class_name" class="field-label">Lớp học <span class="text-red-500">*</span></label>
                        <input type="text" id="class_name" name="class_name" class="field-input"
                            value="{{ old('class_name', $student->class_name) }}" placeholder="Nhập tên lớp" required>
                        <div class="field-description">
                            <small class="text-gray-600">Ví dụ: CNTT K65, KTPM K64, TH K63</small>
                        </div>
                        @error('class_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="course" class="field-label">Khóa</label>
                        <input type="text" id="course" name="course" class="field-input"
                            value="{{ old('course', $student->course) }}" placeholder="Nhập khóa (VD: K65)">
                        <div class="field-description">
                            <small class="text-gray-600">Ví dụ: K65, K66, K67</small>
                        </div>
                        @error('course')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="academic_year" class="field-label">Niên khóa</label>
                        <input type="text" id="academic_year" name="academic_year" class="field-input"
                            value="{{ old('academic_year', $student->academic_year) }}"
                            placeholder="Nhập niên khóa (VD: 2020-2024)">
                        <div class="field-description">
                            <small class="text-gray-600">Ví dụ: 2020-2024, 2021-2025</small>
                        </div>
                        @error('academic_year')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="major_id" class="field-label">Ngành đào tạo <span class="text-red-500">*</span></label>
                        <select id="major_id" name="major_id" class="field-select" required>
                            <option value="">-- Chọn ngành --</option>
                            @foreach ($majors as $major)
                                <option value="{{ $major->major_id }}"
                                    {{ old('major_id', $student->major_id) == $major->major_id ? 'selected' : '' }}>
                                    {{ $major->major_name }} ({{ $major->major_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('major_id')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="gender" class="field-label">Giới tính</label>
                        <select id="gender" name="gender" class="field-select">
                            <option value="">-- Chọn giới tính --</option>
                            @if (enum_exists('App\Enums\StudentGender'))
                                @foreach (\App\Enums\StudentGender::cases() as $gender)
                                    <option value="{{ $gender->value }}"
                                        {{ old('gender', $student->gender?->value) == $gender->value ? 'selected' : '' }}>
                                        {{ $gender->label() }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('gender')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="place_of_birth" class="field-label">Nơi sinh <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="place_of_birth" name="place_of_birth" class="field-input"
                            value="{{ old('place_of_birth', $student->place_of_birth) }}" placeholder="Nhập nơi sinh"
                            required>
                        <div class="field-description">
                            <small class="text-gray-600">Ví dụ: Hà Nội, TP. Hồ Chí Minh, Đà Nẵng</small>
                        </div>
                        @error('place_of_birth')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="status" class="field-label">Trạng thái học tập <span
                                class="text-red-500">*</span></label>
                        <select id="status" name="status" class="field-select" required>
                            <option value="">-- Chọn trạng thái --</option>
                            @foreach (\App\Enums\StudentStatus::cases() as $status)
                                <option value="{{ $status->value }}"
                                    {{ old('status', $student->status?->value) == $status->value ? 'selected' : '' }}>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        <div class="field-description">
                            <small class="text-gray-600">
                                <i class="fas fa-info-circle"></i>
                                Trạng thái này ảnh hưởng đến khả năng cấp văn bằng cho sinh viên
                            </small>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="nation" class="field-label">Dân tộc <span class="text-red-500">*</span></label>
                        <input type="text" id="nation" name="nation" class="field-input"
                            value="{{ old('nation', $student->nation) }}" placeholder="Nhập dân tộc" required>
                        <div class="field-description">
                            <small class="text-gray-600">Ví dụ: Kinh, Tày, Thái, Mường</small>
                        </div>
                        @error('nation')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="nationality" class="field-label">Quốc tịch <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="nationality" name="nationality" class="field-input"
                            value="{{ old('nationality', $student->nationality) }}" placeholder="Nhập quốc tịch"
                            required>
                        <div class="field-description">
                            <small class="text-gray-600">Ví dụ: Việt Nam, Hoa Kỳ, Nhật Bản</small>
                        </div>
                        @error('nationality')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="number_in_the_book" class="field-label">Số sổ gốc <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="number_in_the_book" name="number_in_the_book" class="field-input"
                            value="{{ old('number_in_the_book', $student->number_in_the_book) }}"
                            placeholder="Nhập số sổ gốc" required>
                        <div class="field-description">
                            <small class="text-gray-600">Số thứ tự trong sổ gốc cấp văn bằng (VD: 001, 002, 123)</small>
                        </div>
                        @error('number_in_the_book')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Status Information Section -->
            <div class="section-card">
                <h2 class="section-title">
                    <i class="fas fa-info-circle text-blue-600"></i>
                    Thông tin trạng thái
                </h2>
                <div class="status-info-grid">
                    <div class="status-card">
                        <div class="status-header">
                            <i class="fas fa-user-clock"></i>
                            <span>Trạng thái hiện tại</span>
                        </div>
                        <div class="status-value">
                            @if ($student->status)
                                <span class="status-badge status-{{ strtolower($student->status->name) }}">
                                    {{ $student->status->label() }}
                                </span>
                            @else
                                <span class="status-badge status-unknown">Chưa xác định</span>
                            @endif
                        </div>
                    </div>

                    <div class="status-card">
                        <div class="status-header">
                            <i class="fas fa-graduation-cap"></i>
                            <span>Văn bằng đã cấp</span>
                        </div>
                        <div class="status-value">
                            <span class="text-2xl font-bold text-green-600">{{ $degrees->count() }}</span>
                            <small class="text-gray-500">văn bằng</small>
                        </div>
                    </div>

                    <div class="status-card">
                        <div class="status-header">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Ngày cập nhật</span>
                        </div>
                        <div class="status-value">
                            <span
                                class="text-sm">{{ $student->updated_at?->format('d/m/Y H:i') ?? 'Chưa cập nhật' }}</span>
                        </div>
                    </div>
                </div>

                @if ($student->status && $student->status->value === 2)
                    <div class="alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <strong>Lưu ý:</strong> Sinh viên này đã bỏ học. Không thể cấp văn bằng mới.
                        </div>
                    </div>
                @elseif($student->status && $student->status->value === 0)
                    <div class="alert-info">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Thông tin:</strong> Sinh viên đang học. Chỉ có thể cấp văn bằng khi đã tốt nghiệp.
                        </div>
                    </div>
                @endif
            </div>

            <!-- Degrees Information Section -->
            <div class="section-card">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="section-title mb-0">
                        <i class="fas fa-graduation-cap text-green-600"></i>
                        Văn bằng đã cấp
                        <span class="ml-2 text-sm font-normal text-gray-500">
                            ({{ $degrees->count() }} văn bằng)
                        </span>
                    </h2>
                    @if ($student->status && $student->status->value === 1)
                        <button type="button" onclick="openAddDegreeModal()" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-2"></i>
                            Thêm văn bằng
                        </button>
                    @else
                        <button type="button" class="btn btn-secondary btn-sm cursor-not-allowed opacity-50" disabled
                            title="Chỉ có thể thêm văn bằng cho sinh viên đã tốt nghiệp">
                            <i class="fas fa-plus mr-2"></i>
                            Thêm văn bằng
                        </button>
                    @endif
                </div>
                @if ($degrees->count() > 0)
                    <div class="space-y-4">
                        @foreach ($degrees as $index => $degree)
                            <div class="degree-card">
                                <div class="degree-header">
                                    <div>
                                        <h3>
                                            Văn bằng #{{ $index + 1 }}
                                            @if ($degree->degree_type)
                                                <span class="degree-type-badge">
                                                    {{ $degree->degree_type == 'bachelor'
                                                        ? 'Cử nhân'
                                                        : ($degree->degree_type == 'master'
                                                            ? 'Thạc sĩ'
                                                            : ($degree->degree_type == 'doctor'
                                                                ? 'Tiến sĩ'
                                                                : 'Chứng chỉ')) }}
                                                </span>
                                            @endif
                                        </h3>
                                        <small>
                                            Cấp ngày: {{ $degree->granting_date?->format('d/m/Y') ?? 'Chưa cập nhật' }}
                                        </small>
                                    </div>

                                    {{-- Action Button --}}
                                    <div class="degree-actions">
                                        {{-- Export diploma verification button --}}
                                        <a href="{{ route('student.export-verification', $student->student_id) }}"
                                            class="inline-flex items-center rounded border border-blue-300 bg-white px-3 py-1.5 text-xs font-medium text-blue-700 shadow-sm hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                            title="Xuất công văn xác minh văn bằng">
                                            <i class="fas fa-file-word mr-1"></i>
                                            Xác minh
                                        </a>

                                        {{-- Export bachelor confirmation button --}}
                                        <a href="{{ route('student.export-bachelor-confirmation', $student->student_id) }}"
                                            class="inline-flex items-center rounded border border-green-300 bg-white px-3 py-1.5 text-xs font-medium text-green-700 shadow-sm hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                            title="Xuất giấy xác nhận cử nhân">
                                            <i class="fas fa-file-word mr-1"></i>
                                            Cử nhân
                                        </a>

                                        <button type="button" onclick="openEditDegreeModal({{ $degree->degree_id }})"
                                            class="inline-flex items-center rounded border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                            <i class="fas fa-edit mr-1"></i>
                                            Sửa
                                        </button>
                                        <button type="button"
                                            onclick="confirmDeleteDegree({{ $degree->degree_id }}, '{{ $degree->registration_number }}')"
                                            class="inline-flex items-center rounded border border-red-300 bg-white px-3 py-1.5 text-xs font-medium text-red-700 shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                            <i class="fas fa-trash mr-1"></i>
                                            Xóa
                                        </button>
                                    </div>
                                </div>

                                <div class="degree-details">
                                    <div class="detail-item">
                                        <span class="label">Số đăng ký:</span>
                                        <span class="value">{{ $degree->registration_number ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Năm tốt nghiệp:</span>
                                        <span class="value">{{ $degree->graduation_year ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Xếp loại:</span>
                                        <span class="value">{{ $degree->ranking ?? 'Chưa xếp loại' }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Số quyết định:</span>
                                        <span class="value">{{ $degree->decision_number ?? 'N/A' }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Chuyên ngành:</span>
                                        <span class="value">
                                            @if ($degree->major)
                                                {{ $degree->major->major_name }}
                                            @elseif($degree->major_name)
                                                {{ $degree->major_name }}
                                            @else
                                                N/A
                                            @endif
                                        </span>
                                    </div>
                                    @if ($degree->training_start_date)
                                        <div class="detail-item">
                                            <span class="label">Thời gian đào tạo từ:</span>
                                            <span
                                                class="value">{{ $degree->training_start_date->format('d/m/Y') }}</span>
                                        </div>
                                    @endif
                                    @if ($degree->training_end_date)
                                        <div class="detail-item">
                                            <span class="label">Thời gian đào tạo đến:</span>
                                            <span class="value">{{ $degree->training_end_date->format('d/m/Y') }}</span>
                                        </div>
                                    @endif
                                    <div class="detail-item">
                                        <span class="label">Mã phôi:</span>
                                        <span class="value">
                                            @if ($degree->diplomaBlank)
                                                <code
                                                    class="rounded bg-gray-100 px-2 py-1 text-sm">{{ $degree->diplomaBlank->serial_number }}</code>
                                            @else
                                                <span class="text-gray-500">N/A</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Loại phôi:</span>
                                        <span class="value">
                                            @if ($degree->diplomaBlank && $degree->diplomaBlank->type)
                                                <span
                                                    class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                                    {{ $degree->diplomaBlank->type->type_name }}
                                                </span>
                                            @else
                                                <span class="text-gray-500">N/A</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                            </svg>

                        </div>
                        <p class="empty-title">Chưa có văn bằng nào được cấp</p>
                        <p class="empty-description">Sinh viên này chưa được cấp văn bằng nào</p>
                    </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="action-section">
                <div class="last-updated">
                    <i class="fas fa-info-circle"></i>
                    Cập nhật lần cuối: {{ $student->updated_at?->format('d/m/Y H:i') ?? 'Chưa cập nhật' }}
                </div>
                <div class="action-buttons">
                    <button type="button" onclick="history.back()" class="btn-cancel">
                        <i class="fas fa-times mr-2"></i>Hủy
                    </button>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save mr-2"></i>Cập nhật thông tin
                    </button>
                </div>
            </div>
        </form>

        @if (session('success'))
            <div class="flash-message success" id="success-message">
                <div class="flash-content">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>{{ session('success') }}</span>
                    <button onclick="document.getElementById('success-message').remove()" class="flash-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="flash-message error" id="error-message">
                <div class="flash-content">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span>{{ session('error') }}</span>
                    <button onclick="document.getElementById('error-message').remove()" class="flash-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Add Degree Modal -->
    <div id="addDegreeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="mx-4 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-graduation-cap mr-2 text-green-600"></i>
                    Thêm văn bằng mới
                </h3>
                <button type="button" onclick="closeAddDegreeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="addDegreeForm" method="POST" action="{{ route('degrees.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->student_id }}">

                {{-- Display validation errors --}}
                @if ($errors->any())
                    <div class="rounded-md border border-red-200 bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Có lỗi xảy ra:</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-inside list-disc space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="field-group">
                        <label for="degree_type" class="field-label required">Loại văn bằng</label>
                        <select name="degree_type" id="degree_type" class="field-input" required>
                            <option value="">Chọn loại văn bằng</option>
                            <option value="bachelor" {{ old('degree_type') == 'bachelor' ? 'selected' : '' }}>Cử nhân
                            </option>
                            <option value="master" {{ old('degree_type') == 'master' ? 'selected' : '' }}>Thạc sĩ</option>
                            <option value="doctor" {{ old('degree_type') == 'doctor' ? 'selected' : '' }}>Tiến sĩ</option>
                            <option value="certificate" {{ old('degree_type') == 'certificate' ? 'selected' : '' }}>Chứng
                                chỉ</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label for="diploma_blank_type_id" class="field-label required">Loại phôi văn bằng</label>
                        <select name="diploma_blank_type_id" id="diploma_blank_type_id" class="field-input" required
                            onchange="loadAvailableDiplomaBlanks()">
                            <option value="">Chọn loại phôi văn bằng</option>
                            @foreach ($diplomaBlankTypes as $type)
                                <option value="{{ $type->type_id }}"
                                    {{ old('diploma_blank_type_id') == $type->type_id ? 'selected' : '' }}>
                                    {{ $type->type_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field-group">
                        <label for="selected_blank_info" class="field-label">Phôi văn bằng được chọn</label>
                        <div id="selected_blank_display" class="field-input bg-gray-50 text-gray-700"
                            style="min-height: 42px; display: flex; align-items: center; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                            <span id="blank_placeholder">Chọn loại phôi để xem phôi được gán tự động</span>
                        </div>
                        <input type="hidden" name="diploma_blank_id" id="diploma_blank_id"
                            value="{{ old('diploma_blank_id') }}" required>
                        <div id="diploma_blank_info" class="mt-2 hidden text-sm text-gray-600">
                            <i class="fas fa-info-circle text-blue-500"></i>
                            <span id="blank_info_text"></span>
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="registration_number" class="field-label required">Số đăng ký</label>
                        <input type="text" name="registration_number" id="registration_number" class="field-input"
                            placeholder="Nhập số đăng ký" value="{{ old('registration_number') }}" required>
                    </div>

                    <div class="field-group">
                        <label for="graduation_year" class="field-label required">Năm tốt nghiệp</label>
                        <input type="number" name="graduation_year" id="graduation_year" class="field-input"
                            placeholder="Ví dụ: 2024" min="1990" max="{{ date('Y') }}"
                            value="{{ old('graduation_year') }}" required>
                    </div>

                    <div class="field-group">
                        <x-vietnamese-date-input id="granting_date" name="granting_date" label="Ngày cấp"
                            :required="true" value="{{ old('granting_date') }}" inputClass="field-input" />
                    </div>

                    <div class="field-group">
                        <x-vietnamese-date-input id="defense_date" name="defense_date" label="Ngày bảo vệ"
                            :required="false" value="{{ old('defense_date') }}" inputClass="field-input" />
                    </div>

                    <div class="field-group">
                        <x-vietnamese-date-input id="training_start_date" name="training_start_date"
                            label="Thời gian đào tạo từ ngày" :required="false" value="{{ old('training_start_date') }}"
                            inputClass="field-input" />
                    </div>

                    <div class="field-group">
                        <x-vietnamese-date-input id="training_end_date" name="training_end_date"
                            label="Thời gian đào tạo đến ngày" :required="false" value="{{ old('training_end_date') }}"
                            inputClass="field-input" />
                    </div>

                    <div class="field-group">
                        <label for="ranking" class="field-label">Xếp loại</label>
                        <select name="ranking" id="ranking" class="field-input">
                            <option value="">Chọn xếp loại</option>
                            <option value="Xuất sắc" {{ old('ranking') == 'Xuất sắc' ? 'selected' : '' }}>Xuất sắc
                            </option>
                            <option value="Giỏi" {{ old('ranking') == 'Giỏi' ? 'selected' : '' }}>Giỏi</option>
                            <option value="Khá" {{ old('ranking') == 'Khá' ? 'selected' : '' }}>Khá</option>
                            <option value="Trung bình" {{ old('ranking') == 'Trung bình' ? 'selected' : '' }}>Trung bình
                            </option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label for="decision_number" class="field-label">Số quyết định</label>
                        <input type="text" name="decision_number" id="decision_number" class="field-input"
                            placeholder="Nhập số quyết định" value="{{ old('decision_number') }}">
                    </div>
                </div>

                <div class="field-group">
                    <label for="major_id" class="field-label">Chuyên ngành</label>
                    <select name="major_id" id="major_id" class="field-input">
                        <option value="">Chọn chuyên ngành</option>
                        @foreach ($majors as $major)
                            <option value="{{ $major->major_id }}"
                                {{ old('major_id', $student->major_id) == $major->major_id ? 'selected' : '' }}>
                                {{ $major->major_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label for="notes" class="field-label">Ghi chú</label>
                    <textarea name="notes" id="notes" rows="3" class="field-input" placeholder="Nhập ghi chú (tùy chọn)">{{ old('notes') }}</textarea>
                </div>

                <div class="flex justify-end space-x-3 border-t pt-4">
                    <button type="button" onclick="closeAddDegreeModal()"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-gray-600 hover:bg-gray-50">
                        <i class="fas fa-times mr-2"></i>Hủy
                    </button>
                    <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-white hover:bg-green-700">
                        <i class="fas fa-save mr-2"></i>Lưu văn bằng
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Degree Modal -->
    <div id="editDegreeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="mx-4 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-edit mr-2 text-blue-600"></i>
                    Chỉnh sửa văn bằng
                </h3>
                <button type="button" onclick="closeEditDegreeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="editDegreeForm" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="student_id" value="{{ $student->student_id }}">

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="field-group">
                        <label for="edit_degree_type" class="field-label required">Loại văn bằng</label>
                        <select name="degree_type" id="edit_degree_type" class="field-input" required>
                            <option value="">Chọn loại văn bằng</option>
                            <option value="bachelor">Cử nhân</option>
                            <option value="master">Thạc sĩ</option>
                            <option value="doctor">Tiến sĩ</option>
                            <option value="certificate">Chứng chỉ</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label for="edit_registration_number" class="field-label required">Số đăng ký</label>
                        <input type="text" name="registration_number" id="edit_registration_number"
                            class="field-input" placeholder="Nhập số đăng ký" required>
                    </div>

                    <div class="field-group">
                        <label for="edit_graduation_year" class="field-label required">Năm tốt nghiệp</label>
                        <input type="number" name="graduation_year" id="edit_graduation_year" class="field-input"
                            placeholder="Ví dụ: 2024" min="1990" max="{{ date('Y') }}" required>
                    </div>

                    <div class="field-group">
                        <x-vietnamese-date-input id="edit_granting_date" name="granting_date" label="Ngày cấp"
                            :required="true" value="" inputClass="field-input" />
                    </div>

                    <div class="field-group">
                        <x-vietnamese-date-input id="edit_defense_date" name="defense_date" label="Ngày bảo vệ"
                            :required="false" value="" inputClass="field-input" />
                    </div>

                    <div class="field-group">
                        <x-vietnamese-date-input id="edit_training_start_date" name="training_start_date"
                            label="Thời gian đào tạo từ ngày" :required="false" value=""
                            inputClass="field-input" />
                    </div>

                    <div class="field-group">
                        <x-vietnamese-date-input id="edit_training_end_date" name="training_end_date"
                            label="Thời gian đào tạo đến ngày" :required="false" value=""
                            inputClass="field-input" />
                    </div>

                    <div class="field-group">
                        <label for="edit_ranking" class="field-label">Xếp loại</label>
                        <select name="ranking" id="edit_ranking" class="field-input">
                            <option value="">Chọn xếp loại</option>
                            <option value="Xuất sắc">Xuất sắc</option>
                            <option value="Giỏi">Giỏi</option>
                            <option value="Khá">Khá</option>
                            <option value="Trung bình">Trung bình</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label for="edit_decision_number" class="field-label">Số quyết định</label>
                        <input type="text" name="decision_number" id="edit_decision_number" class="field-input"
                            placeholder="Nhập số quyết định">
                    </div>
                </div>

                <div class="field-group">
                    <label for="edit_major_id" class="field-label">Chuyên ngành</label>
                    <select name="major_id" id="edit_major_id" class="field-input">
                        <option value="">Chọn chuyên ngành</option>
                        @foreach ($majors as $major)
                            <option value="{{ $major->major_id }}">{{ $major->major_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label for="edit_notes" class="field-label">Ghi chú</label>
                    <textarea name="notes" id="edit_notes" rows="3" class="field-input" placeholder="Nhập ghi chú (tùy chọn)"></textarea>
                </div>

                <div class="rounded-md border border-blue-200 bg-blue-50 p-3">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                        <span class="text-sm font-medium text-blue-800">Thông tin phôi văn bằng</span>
                    </div>
                    <div class="mt-2 text-sm text-blue-700">
                        <div><strong>Mã phôi:</strong> <code id="edit_diploma_serial"
                                class="rounded bg-blue-100 px-2 py-1">-</code></div>
                        <div class="mt-1"><strong>Loại phôi:</strong> <span id="edit_diploma_type"
                                class="inline-flex items-center rounded-full bg-blue-200 px-2 py-0.5 text-xs font-medium text-blue-800">-</span>
                        </div>
                        <div class="mt-2 text-xs text-blue-600">
                            <i class="fas fa-lock mr-1"></i>
                            Không thể thay đổi phôi văn bằng sau khi đã cấp
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 border-t pt-4">
                    <button type="button" onclick="closeEditDegreeModal()"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-gray-600 hover:bg-gray-50">
                        <i class="fas fa-times mr-2"></i>Hủy
                    </button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Cập nhật văn bằng
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-hide success/error messages after 5 seconds
        setTimeout(() => {
            const successMsg = document.getElementById('success-message');
            const errorMsg = document.getElementById('error-message');
            if (successMsg) successMsg.remove();
            if (errorMsg) errorMsg.remove();
        }, 5000);

        // Form validation and status change handling
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const requiredFields = form.querySelectorAll('input[required], select[required]');
            const statusSelect = document.getElementById('status');
            const currentDegreeCount = {{ $degrees->count() }};

            // Open modal if there are validation errors
            @if ($errors->any())
                openAddDegreeModal();
            @endif

            // Handle status change warnings
            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    const selectedValue = parseInt(this.value);
                    const currentValue = {{ $student->status?->value ?? 'null' }};

                    // Show warning when changing to "Bỏ học" (value = 2)
                    if (selectedValue === 2 && currentValue !== 2) {
                        if (!confirm(
                                '⚠️ Bạn có chắc muốn chuyển trạng thái thành "Bỏ học"?\n\nLưu ý: Sinh viên bỏ học sẽ không thể cấp văn bằng mới.'
                            )) {
                            this.value = currentValue !== null ? currentValue : '';
                            return;
                        }
                    }

                    // Show warning when changing from "Đã tốt nghiệp" to other status if student has degrees
                    if (currentValue === 1 && selectedValue !== 1 && currentDegreeCount > 0) {
                        if (!confirm('⚠️ Sinh viên này đã có ' + currentDegreeCount +
                                ' văn bằng được cấp.\n\nBạn có chắc muốn thay đổi trạng thái khỏi "Đã tốt nghiệp"?'
                            )) {
                            this.value = currentValue;
                            return;
                        }
                    }

                    // Show success message when changing to "Đã tốt nghiệp"
                    if (selectedValue === 1 && currentValue !== 1) {
                        showNotification('✅ Sinh viên sẽ có thể được cấp văn bằng sau khi cập nhật.',
                            'success');
                    }
                });
            }

            // Form validation
            form.addEventListener('submit', function(e) {
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('border-red-500');

                        // Remove error styling on input
                        field.addEventListener('input', function() {
                            this.classList.remove('border-red-500');
                        });
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
                }
            });
        });

        // Show notification function
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `flash-message ${type}`;
            notification.innerHTML = `
                <div class="flash-content">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="flash-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            document.body.appendChild(notification);

            // Auto-hide after 4 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 4000);
        }

        // Add Degree Modal Functions
        function loadAvailableDiplomaBlanks() {
            const typeSelect = document.getElementById('diploma_blank_type_id');
            const blankIdInput = document.getElementById('diploma_blank_id');
            const displayDiv = document.getElementById('selected_blank_display');
            const placeholderSpan = document.getElementById('blank_placeholder');
            const infoDiv = document.getElementById('diploma_blank_info');

            if (!typeSelect.value) {
                // Reset when no type selected
                placeholderSpan.textContent = 'Chọn loại phôi để xem phôi được gán tự động';
                blankIdInput.value = '';
                infoDiv.classList.add('hidden');
                return;
            }

            // Show loading
            placeholderSpan.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang tìm phôi cũ nhất...';

            fetch(`/api/diploma-blanks/available/${typeSelect.value}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.blank) {
                        // Show selected blank info
                        placeholderSpan.innerHTML = `
                            <div class="flex items-center justify-between w-full">
                                <div>
                                    <strong class="text-green-600">${data.blank.serial_number}</strong>
                                    <span class="text-gray-500 ml-2">Ngày nhập: ${data.blank.import_date}</span>
                                </div>
                                <i class="fas fa-check-circle text-green-500"></i>
                            </div>
                        `;

                        // Set hidden input value
                        blankIdInput.value = data.blank.diploma_blank_id;

                        // Show success message
                        document.getElementById('blank_info_text').textContent = data.message;
                        infoDiv.classList.remove('hidden');
                    } else {
                        // No blank available
                        placeholderSpan.innerHTML = `
                            <div class="flex items-center text-red-600">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Không có phôi khả dụng cho loại này
                            </div>
                        `;
                        blankIdInput.value = '';
                        document.getElementById('blank_info_text').textContent =
                            data.message || 'Không có phôi khả dụng cho loại này';
                        infoDiv.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    placeholderSpan.innerHTML = `
                        <div class="flex items-center text-red-600">
                            <i class="fas fa-times-circle mr-2"></i>
                            Lỗi tải dữ liệu phôi
                        </div>
                    `;
                    blankIdInput.value = '';
                    document.getElementById('blank_info_text').textContent =
                        'Có lỗi xảy ra khi tải thông tin phôi';
                    infoDiv.classList.remove('hidden');
                });
        }

        function openAddDegreeModal() {
            const modal = document.getElementById('addDegreeModal');

            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        }

        function closeAddDegreeModal() {
            const modal = document.getElementById('addDegreeModal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
            // Reset form
            document.getElementById('addDegreeForm').reset();

            // Reset diploma blank display
            document.getElementById('blank_placeholder').textContent = 'Chọn loại phôi để xem phôi được gán tự động';
            document.getElementById('diploma_blank_id').value = '';

            // Hide info
            document.getElementById('diploma_blank_info').classList.add('hidden');
        }

        // Edit Degree Modal Functions
        function openEditDegreeModal(degreeId) {
            // Get degree data from PHP and populate form
            const degrees = @json($degrees);
            const degree = degrees.find(d => d.degree_id == degreeId);

            if (!degree) {
                alert('Không tìm thấy thông tin văn bằng!');
                return;
            }

            // Update form action
            document.getElementById('editDegreeForm').action = `/degrees/${degreeId}/update`;

            // Populate form fields
            document.getElementById('edit_degree_type').value = degree.degree_type || '';
            document.getElementById('edit_registration_number').value = degree.registration_number || '';
            document.getElementById('edit_graduation_year').value = degree.graduation_year || '';

            // Set granting date for both hidden input and display input
            const grantingDate = degree.granting_date ? degree.granting_date.split('T')[0] : '';
            document.getElementById('edit_granting_date').value = grantingDate;

            // Format and set display input
            if (grantingDate) {
                const displayInput = document.getElementById('edit_granting_date_display');
                if (displayInput) {
                    const date = new Date(grantingDate);
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    displayInput.value = `${day}/${month}/${year}`;
                }
            }

            // Set defense date
            const defenseDate = degree.defense_date ? degree.defense_date.split('T')[0] : '';
            document.getElementById('edit_defense_date').value = defenseDate;

            // Format and set display input for defense date
            if (defenseDate) {
                const displayInput = document.getElementById('edit_defense_date_display');
                if (displayInput) {
                    const date = new Date(defenseDate);
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    displayInput.value = `${day}/${month}/${year}`;
                }
            }

            // Set training start date
            const trainingStartDate = degree.training_start_date ? degree.training_start_date.split('T')[0] : '';
            document.getElementById('edit_training_start_date').value = trainingStartDate;

            // Format and set display input for training start date
            if (trainingStartDate) {
                const displayInput = document.getElementById('edit_training_start_date_display');
                if (displayInput) {
                    const date = new Date(trainingStartDate);
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    displayInput.value = `${day}/${month}/${year}`;
                }
            }

            // Set training end date
            const trainingEndDate = degree.training_end_date ? degree.training_end_date.split('T')[0] : '';
            document.getElementById('edit_training_end_date').value = trainingEndDate;

            // Format and set display input for training end date
            if (trainingEndDate) {
                const displayInput = document.getElementById('edit_training_end_date_display');
                if (displayInput) {
                    const date = new Date(trainingEndDate);
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    displayInput.value = `${day}/${month}/${year}`;
                }
            }

            document.getElementById('edit_ranking').value = degree.ranking || '';
            document.getElementById('edit_decision_number').value = degree.decision_number || '';
            document.getElementById('edit_major_id').value = degree.major_id || '';
            document.getElementById('edit_notes').value = degree.notes || '';

            // Update diploma blank info
            if (degree.diploma_blank) {
                document.getElementById('edit_diploma_serial').textContent = degree.diploma_blank.serial_number || '-';
                document.getElementById('edit_diploma_type').textContent =
                    (degree.diploma_blank.type && degree.diploma_blank.type.type_name) || '-';
            } else {
                document.getElementById('edit_diploma_serial').textContent = '-';
                document.getElementById('edit_diploma_type').textContent = '-';
            }

            // Show modal
            const editModal = document.getElementById('editDegreeModal');
            editModal.classList.remove('hidden');
            editModal.style.display = 'flex';
        }

        function closeEditDegreeModal() {
            const editModal = document.getElementById('editDegreeModal');
            editModal.classList.add('hidden');
            editModal.style.display = 'none';
            // Reset form
            document.getElementById('editDegreeForm').reset();
        }

        // Delete Degree Functions
        function confirmDeleteDegree(degreeId, registrationNumber) {
            if (confirm(
                    `⚠️ Bạn có chắc muốn xóa văn bằng "${registrationNumber}"?\n\nLưu ý: Văn bằng sẽ được xóa mềm và có thể khôi phục sau này.`
                )) {
                deleteDegree(degreeId);
            }
        }

        function deleteDegree(degreeId) {
            // Create form for DELETE request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/degrees/${degreeId}/delete`;

            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            // Add method spoofing for DELETE
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            // Submit form
            document.body.appendChild(form);
            form.submit();
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const addModal = document.getElementById('addDegreeModal');
            const editModal = document.getElementById('editDegreeModal');

            if (event.target === addModal) {
                closeAddDegreeModal();
            }

            if (event.target === editModal) {
                closeEditDegreeModal();
            }
        });
    </script>
@endsection
