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

        /* Degree Status Badges */
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-completed {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-processing {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-failed {
            background-color: #fee2e2;
            color: #dc2626;
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
        #editDegreeModal,
        #addReissueModal,
        #deleteDegreeModal {
            z-index: 9999;
        }

        #addDegreeModal.hidden,
        #editDegreeModal.hidden,
        #addReissueModal.hidden,
        #deleteDegreeModal.hidden {
            display: none !important;
        }

        #addDegreeModal:not(.hidden),
        #editDegreeModal:not(.hidden),
        #addReissueModal:not(.hidden),
        #deleteDegreeModal:not(.hidden) {
            display: flex !important;
        }

        #addDegreeModal .bg-white,
        #editDegreeModal .bg-white,
        #addReissueModal .bg-white,
        #deleteDegreeModal .bg-white {
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

        /* Adjustment History Collapse Styles */
        .adjustment-history-toggle {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .adjustment-history-toggle:hover {
            background-color: #f3f4f6;
        }

        .adjustment-history-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .adjustment-history-content.expanded {
            max-height: 400px;
            overflow-y: auto;
        }

        .toggle-icon {
            transition: transform 0.3s ease;
        }

        .toggle-icon.rotated {
            transform: rotate(180deg);
        }

        /* Custom scrollbar for adjustment history */
        .adjustment-history-content::-webkit-scrollbar {
            width: 8px;
        }

        .adjustment-history-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .adjustment-history-content::-webkit-scrollbar-thumb {
            background: #a78bfa;
            border-radius: 4px;
        }

        .adjustment-history-content::-webkit-scrollbar-thumb:hover {
            background: #8b5cf6;
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
                        <label for="hometown" class="field-label">Quê quán</label>
                        <input type="text" id="hometown" name="hometown" class="field-input"
                            value="{{ old('hometown', $student->hometown) }}" placeholder="Nhập quê quán">
                        <div class="field-description">
                            <small class="text-gray-600">Ví dụ: Hà Nội, Nghệ An, Thanh Hóa</small>
                        </div>
                        @error('hometown')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="place_of_origin" class="field-label">Nguyên quán</label>
                        <input type="text" id="place_of_origin" name="place_of_origin" class="field-input"
                            value="{{ old('place_of_origin', $student->place_of_origin) }}"
                            placeholder="Nhập nguyên quán">
                        <div class="field-description">
                            <small class="text-gray-600">Ví dụ: Hà Nội, Hải Phòng, Huế</small>
                        </div>
                        @error('place_of_origin')
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

                    <div class="status-card">
                        <div class="status-header">
                            <i class="fas fa-history"></i>
                            <span>Lịch sử thay đổi</span>
                        </div>
                        <div class="status-value">
                            <span class="text-2xl font-bold text-purple-600">{{ $student->changeLogs()->count() }}</span>
                            <small class="text-gray-500">lần thay đổi</small>
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

            <!-- Student Change History Section -->
            @php
                $studentChangeLogs = $student->changeLogs()->with('changedBy')->latest()->get();
            @endphp
            @if ($studentChangeLogs->count() > 0)
                <div class="section-card">
                    <div class="mb-4">
                        <div class="adjustment-history-toggle mb-2 flex items-center justify-between rounded-lg p-2"
                            onclick="toggleStudentHistory()">
                            <h2 class="section-title mb-0">
                                <i class="fas fa-history text-purple-600"></i>
                                Lịch sử thay đổi thông tin sinh viên
                                <span class="ml-2 text-sm font-normal text-gray-500">
                                    ({{ $studentChangeLogs->count() }} lần thay đổi)
                                </span>
                            </h2>
                            <i class="fas fa-chevron-down toggle-icon text-gray-500" id="toggle-icon-student"></i>
                        </div>
                        <div class="adjustment-history-content space-y-2" id="student-change-history">
                            @foreach ($studentChangeLogs as $log)
                                <div class="flex gap-3 rounded-lg bg-blue-50 p-3 text-sm">
                                    <div class="flex-shrink-0">
                                        @if ($log->action_type === 'create')
                                            <i class="fas fa-plus-circle text-green-600"></i>
                                        @elseif($log->action_type === 'update')
                                            <i class="fas fa-edit text-blue-600"></i>
                                        @elseif($log->action_type === 'delete')
                                            <i class="fas fa-trash text-red-600"></i>
                                        @else
                                            <i class="fas fa-undo text-purple-600"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        @php
                                            $actionLabels = [
                                                'create' => ['label' => 'Tạo mới', 'color' => 'green'],
                                                'update' => ['label' => 'Cập nhật', 'color' => 'blue'],
                                                'delete' => ['label' => 'Xóa', 'color' => 'red'],
                                                'restore' => ['label' => 'Khôi phục', 'color' => 'purple'],
                                            ];
                                            $action = $actionLabels[$log->action_type] ?? [
                                                'label' => 'Khác',
                                                'color' => 'gray',
                                            ];
                                        @endphp
                                        <div class="mb-2 flex items-center gap-2">
                                            <span
                                                class="bg-{{ $action['color'] }}-100 text-{{ $action['color'] }}-700 rounded px-2 py-0.5 text-xs font-semibold">
                                                {{ $action['label'] }}
                                            </span>
                                            @if ($log->changed_field)
                                                <span class="text-xs font-semibold text-blue-700">
                                                    <i class="fas fa-tag mr-1"></i>
                                                    {{ ucfirst($log->changed_field) }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($log->old_value && $log->new_value)
                                            <p class="mb-2 text-sm">
                                                <span
                                                    class="rounded bg-red-100 px-2 py-0.5 text-red-700 line-through">{{ $log->old_value }}</span>
                                                <i class="fas fa-arrow-right mx-2 text-gray-400"></i>
                                                <span
                                                    class="rounded bg-green-100 px-2 py-0.5 font-medium text-green-700">{{ $log->new_value }}</span>
                                            </p>
                                        @endif
                                        <p class="font-medium text-gray-900">{{ $log->change_description }}</p>
                                        <div class="mt-1 flex flex-wrap gap-x-4 text-xs text-gray-600">
                                            @if ($log->changedBy)
                                                <span>
                                                    <i class="fas fa-user mr-1"></i>
                                                    {{ $log->changedBy->full_name }}
                                                </span>
                                            @endif
                                            <span>
                                                <i class="fas fa-clock mr-1"></i>
                                                {{ $log->created_at->diffForHumans() }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ $log->created_at->format('d/m/Y H:i') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

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
                                        @if ($degree->updated_at && $degree->created_at->ne($degree->updated_at))
                                            <small class="text-xs text-gray-500">
                                                <i class="fas fa-edit mr-1 text-purple-600"></i>
                                                Sửa đổi: {{ $degree->updated_at->format('d/m/Y H:i') }}
                                            </small>
                                        @endif
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
                                        <button type="button" onclick="openAdjustmentModal({{ $degree->degree_id }})"
                                            class="inline-flex items-center rounded border border-purple-300 bg-white px-3 py-1.5 text-xs font-medium text-purple-700 shadow-sm hover:bg-purple-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                            <i class="fas fa-history mr-1"></i>
                                            Điều chỉnh
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
                                        <span class="label">Trạng thái:</span>
                                        <span class="value">
                                            @if ($degree->status)
                                                <span class="status-badge {{ $degree->status->getBadgeClass() }}">
                                                    <i class="fas fa-{{ $degree->status->getIcon() }} mr-1"></i>
                                                    {{ $degree->status->getLabel() }}
                                                </span>
                                            @else
                                                <span class="text-gray-500">N/A</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Số quyết định:</span>
                                        <span class="value">{{ $degree->graduation_decision_number ?? 'N/A' }}</span>
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
                                    @if ($degree->training_type)
                                        <div class="detail-item">
                                            <span class="label">Hình thức đào tạo:</span>
                                            <span class="value">{{ $degree->training_type }}</span>
                                        </div>
                                    @endif
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

                                {{-- Adjustment History Section --}}
                                @if ($degree->changeLogs && $degree->changeLogs->count() > 0)
                                    <div class="mt-4 border-t pt-4">
                                        <div class="adjustment-history-toggle mb-2 flex items-center justify-between rounded-lg p-2"
                                            onclick="toggleAdjustmentHistory({{ $degree->degree_id }})">
                                            <h4 class="text-sm font-semibold text-gray-700">
                                                <i class="fas fa-history mr-1 text-purple-600"></i>
                                                Lịch sử điều chỉnh ({{ $degree->changeLogs->count() }})
                                            </h4>
                                            <i class="fas fa-chevron-down toggle-icon text-gray-500"
                                                id="toggle-icon-{{ $degree->degree_id }}"></i>
                                        </div>
                                        <div class="adjustment-history-content space-y-2"
                                            id="adjustment-history-{{ $degree->degree_id }}">
                                            @foreach ($degree->changeLogs->take(3) as $adjustment)
                                                <div class="flex gap-3 rounded-lg bg-purple-50 p-3 text-sm">
                                                    <div class="flex-shrink-0">
                                                        @if (in_array($adjustment->action_type, ['create', 'created']))
                                                            <i class="fas fa-plus-circle text-green-600"></i>
                                                        @elseif(in_array($adjustment->action_type, ['update', 'updated']))
                                                            <i class="fas fa-edit text-purple-600"></i>
                                                        @elseif(in_array($adjustment->action_type, ['delete', 'deleted']))
                                                            <i class="fas fa-trash text-red-600"></i>
                                                        @elseif($adjustment->action_type === 'reissued')
                                                            <i class="fas fa-sync-alt text-blue-600"></i>
                                                        @else
                                                            <i class="fas fa-undo text-blue-600"></i>
                                                        @endif
                                                    </div>
                                                    <div class="flex-1">
                                                        @php
                                                            $actionLabels = [
                                                                'create' => ['label' => 'Tạo mới', 'color' => 'green'],
                                                                'created' => ['label' => 'Tạo mới', 'color' => 'green'],
                                                                'update' => [
                                                                    'label' => 'Cập nhật',
                                                                    'color' => 'purple',
                                                                ],
                                                                'updated' => [
                                                                    'label' => 'Điều chỉnh',
                                                                    'color' => 'purple',
                                                                ],
                                                                'delete' => ['label' => 'Xóa', 'color' => 'red'],
                                                                'deleted' => ['label' => 'Xóa', 'color' => 'red'],
                                                                'restore' => [
                                                                    'label' => 'Khôi phục',
                                                                    'color' => 'blue',
                                                                ],
                                                                'reissued' => ['label' => 'Cấp lại', 'color' => 'blue'],
                                                            ];
                                                            $action = $actionLabels[$adjustment->action_type] ?? [
                                                                'label' => 'Khác',
                                                                'color' => 'gray',
                                                            ];
                                                        @endphp
                                                        <div class="mb-2 flex items-center gap-2">
                                                            <span
                                                                class="bg-{{ $action['color'] }}-100 text-{{ $action['color'] }}-700 rounded px-2 py-0.5 text-xs font-semibold">
                                                                {{ $action['label'] }}
                                                            </span>
                                                            @if ($adjustment->changed_field)
                                                                @php
                                                                    $fieldLabels = [
                                                                        'registration_number' => 'Số đăng ký',
                                                                        'degree_type' => 'Loại văn bằng',
                                                                        'diploma_blank_id' => 'Số hiệu văn bằng',
                                                                        'major_name' => 'Ngành/Chuyên ngành',
                                                                        'ranking' => 'Xếp loại',
                                                                        'granting_date' => 'Ngày cấp',
                                                                        'graduation_year' => 'Năm tốt nghiệp',
                                                                        'decision_number' => 'Số quyết định',
                                                                        'council_decision_number' =>
                                                                            'Số QĐ thành lập hội đồng',
                                                                        'council_decision_date' =>
                                                                            'Ngày QĐ thành lập hội đồng',
                                                                        'graduation_decision_number' =>
                                                                            'Số QĐ công nhận tốt nghiệp',
                                                                        'graduation_decision_date' =>
                                                                            'Ngày QĐ công nhận tốt nghiệp',
                                                                        'defense_date' => 'Ngày bảo vệ',
                                                                        'training_start_date' => 'Ngày bắt đầu đào tạo',
                                                                        'training_end_date' => 'Ngày kết thúc đào tạo',
                                                                    ];
                                                                @endphp
                                                                <span class="text-xs font-semibold text-purple-700">
                                                                    <i class="fas fa-tag mr-1"></i>
                                                                    {{ $fieldLabels[$adjustment->changed_field] ?? $adjustment->changed_field }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <p class="font-medium text-gray-900">
                                                            {{ $adjustment->change_description ?: 'Điều chỉnh thông tin' }}
                                                        </p>
                                                        @if ($adjustment->old_value && $adjustment->new_value)
                                                            @php
                                                                // Map giá trị tiếng Anh sang tiếng Việt
                                                                $valueMapping = [
                                                                    // Degree types
                                                                    'certificate' => 'Chứng chỉ',
                                                                    'bachelor' => 'Cử nhân',
                                                                    'engineer' => 'Kỹ sư',
                                                                    'master' => 'Thạc sĩ',
                                                                    'doctor' => 'Tiến sĩ',
                                                                    // Rankings
                                                                    'excellent' => 'Xuất sắc',
                                                                    'very_good' => 'Giỏi',
                                                                    'good' => 'Khá',
                                                                    'average' => 'Trung bình',
                                                                    'below_average' => 'Trung bình khá',
                                                                ];

                                                                // Nếu field là diploma_blank_id, lấy serial_number thay vì hiển thị ID
                                                                if ($adjustment->changed_field === 'diploma_blank_id') {
                                                                    $oldBlank = \App\Models\DiplomaBlank::find(
                                                                        $adjustment->old_value,
                                                                    );
                                                                    $newBlank = \App\Models\DiplomaBlank::find(
                                                                        $adjustment->new_value,
                                                                    );
                                                                    $oldValueDisplay = $oldBlank
                                                                        ? $oldBlank->serial_number
                                                                        : $adjustment->old_value;
                                                                    $newValueDisplay = $newBlank
                                                                        ? $newBlank->serial_number
                                                                        : $adjustment->new_value;
                                                                } else {
                                                                    $oldValueDisplay =
                                                                        $valueMapping[
                                                                            strtolower($adjustment->old_value)
                                                                        ] ?? $adjustment->old_value;
                                                                    $newValueDisplay =
                                                                        $valueMapping[
                                                                            strtolower($adjustment->new_value)
                                                                        ] ?? $adjustment->new_value;
                                                                }
                                                            @endphp
                                                            <p class="mb-2 mt-2 text-sm">
                                                                <span
                                                                    class="rounded bg-red-100 px-2 py-0.5 text-red-700 line-through">{{ $oldValueDisplay }}</span>
                                                                <i class="fas fa-arrow-right mx-2 text-gray-400"></i>
                                                                <span
                                                                    class="rounded bg-green-100 px-2 py-0.5 font-medium text-green-700">{{ $newValueDisplay }}</span>
                                                            </p>
                                                        @endif
                                                        <div class="mt-1 flex flex-wrap gap-x-4 text-xs text-gray-600">
                                                            @if ($adjustment->decision_number)
                                                                <span>
                                                                    <i class="fas fa-file-contract mr-1"></i>
                                                                    QĐ: {{ $adjustment->decision_number }}
                                                                </span>
                                                            @endif
                                                            @if ($adjustment->decision_date)
                                                                <span>
                                                                    <i class="fas fa-calendar mr-1"></i>
                                                                    {{ $adjustment->decision_date->format('d/m/Y') }}
                                                                </span>
                                                            @endif
                                                            @if ($adjustment->changedBy)
                                                                <span>
                                                                    <i class="fas fa-user mr-1"></i>
                                                                    {{ $adjustment->changedBy->full_name }}
                                                                </span>
                                                            @endif
                                                            <span>
                                                                <i class="fas fa-clock mr-1"></i>
                                                                {{ $adjustment->created_at->diffForHumans() }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if ($degree->changeLogs->count() > 3)
                                                <button type="button"
                                                    onclick="viewAllAdjustments({{ $degree->degree_id }})"
                                                    class="w-full text-center text-sm text-purple-600 hover:text-purple-800">
                                                    Xem tất cả {{ $degree->changeLogs->count() }} lượt điều chỉnh
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Reissue History Section --}}
                                @php
                                    $reissues = $degree->reissues ?? collect();
                                @endphp
                                <div class="mt-4 border-t pt-4">
                                    <div class="adjustment-history-toggle mb-2 flex items-center justify-between rounded-lg p-2"
                                        onclick="toggleReissueHistory({{ $degree->degree_id }})">
                                        <div class="flex items-center gap-3">
                                            <h4 class="text-sm font-semibold text-gray-700">
                                                <i class="fas fa-sync-alt mr-1 text-blue-600"></i>
                                                Lịch sử cấp lại văn bằng ({{ $reissues->count() }})
                                            </h4>
                                            <button type="button"
                                                onclick="event.stopPropagation(); openReissueModal({{ $degree->degree_id }}, {{ $degree->diplomaBlank?->type_id ?? 'null' }}, '{{ $degree->diplomaBlank?->serial_number ?? 'N/A' }}')"
                                                class="inline-flex items-center rounded border border-blue-300 bg-white px-3 py-1.5 text-xs font-medium text-blue-700 shadow-sm hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                <i class="fas fa-plus mr-1"></i>
                                                Cấp lại
                                            </button>
                                        </div>
                                        <i class="fas fa-chevron-down toggle-icon text-gray-500"
                                            id="toggle-icon-reissue-{{ $degree->degree_id }}"></i>
                                    </div>
                                    <div class="adjustment-history-content space-y-2"
                                        id="reissue-history-{{ $degree->degree_id }}">
                                        @if ($reissues->count() > 0)
                                            @foreach ($reissues as $reissue)
                                                <div class="flex gap-3 rounded-lg bg-blue-50 p-3 text-sm">
                                                    <div class="flex-shrink-0">
                                                        <i class="fas fa-file-import text-blue-600"></i>
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="mb-2 flex items-center justify-between">
                                                            <div class="flex items-center gap-2">
                                                                <span
                                                                    class="rounded bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">
                                                                    Cấp lại
                                                                </span>
                                                                <span class="text-xs text-gray-600">
                                                                    <i class="fas fa-calendar mr-1"></i>
                                                                    {{ $reissue->decision_date->format('d/m/Y') }}
                                                                </span>
                                                                @if ($reissue->is_recalled)
                                                                    <span
                                                                        class="rounded bg-yellow-100 px-2 py-0.5 text-xs font-semibold text-yellow-700">
                                                                        <i class="fas fa-undo mr-1"></i>Thu hồi
                                                                    </span>
                                                                @endif
                                                                @if ($reissue->is_destroyed)
                                                                    <span
                                                                        class="rounded bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">
                                                                        <i class="fas fa-ban mr-1"></i>Hủy
                                                                    </span>
                                                                @endif
                                                                @if (!$reissue->is_recalled && !$reissue->is_destroyed)
                                                                    <span
                                                                        class="rounded bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">
                                                                        <i class="fas fa-clock mr-1"></i>Chưa thu hồi
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <p class="mb-1 text-xs text-gray-600">
                                                                <span class="font-semibold">Phôi cũ:</span>
                                                                @if ($reissue->oldDiplomaBlank)
                                                                    <code
                                                                        class="rounded bg-red-100 px-2 py-0.5 text-red-700 line-through">{{ $reissue->oldDiplomaBlank->serial_number }}</code>
                                                                    @if ($reissue->oldDiplomaBlank->type)
                                                                        <span
                                                                            class="text-xs text-gray-500">({{ $reissue->oldDiplomaBlank->type->type_name }})</span>
                                                                    @endif
                                                                @else
                                                                    <span class="text-gray-500">N/A</span>
                                                                @endif
                                                            </p>
                                                            <p class="mb-1 text-xs text-gray-600">
                                                                <span class="font-semibold">Phôi mới:</span>
                                                                @if ($reissue->newDiplomaBlank)
                                                                    <code
                                                                        class="rounded bg-green-100 px-2 py-0.5 font-medium text-green-700">{{ $reissue->newDiplomaBlank->serial_number }}</code>
                                                                    @if ($reissue->newDiplomaBlank->type)
                                                                        <span
                                                                            class="text-xs text-gray-500">({{ $reissue->newDiplomaBlank->type->type_name }})</span>
                                                                    @endif
                                                                @else
                                                                    <span class="text-gray-500">N/A</span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                        <p class="mb-1 text-xs">
                                                            <span class="font-semibold text-gray-700">Nội dung chỉnh
                                                                sửa:</span>
                                                            <span
                                                                class="text-gray-900">{{ $reissue->edit_content }}</span>
                                                        </p>
                                                        <p class="mb-1 text-xs">
                                                            <span class="font-semibold text-gray-700">QĐ thu hồi & cấp
                                                                lại:</span>
                                                            <span
                                                                class="text-gray-900">{{ $reissue->recall_decision }}</span>
                                                        </p>
                                                        @if ($reissue->notes)
                                                            <p class="text-xs text-gray-600">
                                                                <i class="fas fa-sticky-note mr-1"></i>
                                                                {{ $reissue->notes }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="rounded-lg bg-gray-50 p-4 text-center text-sm text-gray-500">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Chưa có lịch sử cấp lại văn bằng
                                            </div>
                                        @endif
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
                            <option value="master" {{ old('degree_type') == 'master' ? 'selected' : '' }}>Thạc sĩ
                            </option>
                            <option value="doctor" {{ old('degree_type') == 'doctor' ? 'selected' : '' }}>Tiến sĩ
                            </option>
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
                        <label for="status" class="field-label">Trạng thái <span class="text-red-500">*</span></label>
                        <select name="status" id="status" class="field-input" required>
                            <option value="">Chọn trạng thái</option>
                            <option value="NotIssued" {{ old('status') == 'NotIssued' ? 'selected' : '' }}>Chưa cấp
                            </option>
                            <option value="Issued" {{ old('status', 'Issued') == 'Issued' ? 'selected' : '' }}>Đã cấp
                            </option>
                            <option value="Recalled" {{ old('status') == 'Recalled' ? 'selected' : '' }}>Thu hồi</option>
                        </select>
                        <p class="field-description text-sm text-gray-600 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Mặc định là "Đã cấp" khi tạo mới văn bằng
                        </p>
                    </div>

                    <div class="field-group">
                        <label for="council_decision_number" class="field-label">Số QĐ thành lập hội đồng</label>
                        <input type="text" name="council_decision_number" id="council_decision_number"
                            class="field-input" placeholder="Nhập số QĐ thành lập hội đồng"
                            value="{{ old('council_decision_number') }}">
                    </div>

                    <div class="field-group">
                        <x-vietnamese-date-input id="council_decision_date" name="council_decision_date"
                            label="Ngày QĐ thành lập hội đồng" :required="false"
                            value="{{ old('council_decision_date') }}" inputClass="field-input" />
                    </div>

                    <div class="field-group">
                        <label for="graduation_decision_number" class="field-label">Số QĐ công nhận tốt nghiệp</label>
                        <input type="text" name="graduation_decision_number" id="graduation_decision_number"
                            class="field-input" placeholder="Nhập số QĐ công nhận tốt nghiệp"
                            value="{{ old('graduation_decision_number') }}">
                    </div>

                    <div class="field-group">
                        <x-vietnamese-date-input id="graduation_decision_date" name="graduation_decision_date"
                            label="Ngày QĐ công nhận tốt nghiệp" :required="false"
                            value="{{ old('graduation_decision_date') }}" inputClass="field-input" />
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
                    <label for="training_type" class="field-label">Hình thức đào tạo</label>
                    <select name="training_type" id="training_type" class="field-input">
                        <option value="">Chọn hình thức đào tạo</option>
                        <option value="Chính quy" {{ old('training_type') == 'Chính quy' ? 'selected' : '' }}>Chính quy
                        </option>
                        <option value="Liên thông" {{ old('training_type') == 'Liên thông' ? 'selected' : '' }}>Liên
                            thông</option>
                        <option value="Từ xa" {{ old('training_type') == 'Từ xa' ? 'selected' : '' }}>Từ xa</option>
                        <option value="Vừa làm vừa học"
                            {{ old('training_type') == 'Vừa làm vừa học' ? 'selected' : '' }}>Vừa làm vừa học</option>
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
                        <label for="edit_status" class="field-label">Trạng thái <span
                                class="text-red-500">*</span></label>
                        <select name="status" id="edit_status" class="field-input" required>
                            <option value="">Chọn trạng thái</option>
                            <option value="NotIssued">Chưa cấp</option>
                            <option value="Issued">Đã cấp</option>
                            <option value="Recalled">Thu hồi</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label for="edit_council_decision_number" class="field-label">Số QĐ thành lập hội đồng</label>
                        <input type="text" name="council_decision_number" id="edit_council_decision_number"
                            class="field-input" placeholder="Nhập số QĐ thành lập hội đồng">
                    </div>

                    <div class="field-group">
                        <x-vietnamese-date-input id="edit_council_decision_date" name="council_decision_date"
                            label="Ngày QĐ thành lập hội đồng" :required="false" value=""
                            inputClass="field-input" />
                    </div>

                    <div class="field-group">
                        <label for="edit_graduation_decision_number" class="field-label">Số QĐ công nhận tốt
                            nghiệp</label>
                        <input type="text" name="graduation_decision_number" id="edit_graduation_decision_number"
                            class="field-input" placeholder="Nhập số QĐ công nhận tốt nghiệp">
                    </div>

                    <div class="field-group">
                        <x-vietnamese-date-input id="edit_graduation_decision_date" name="graduation_decision_date"
                            label="Ngày QĐ công nhận tốt nghiệp" :required="false" value=""
                            inputClass="field-input" />
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
                    <label for="edit_training_type" class="field-label">Hình thức đào tạo</label>
                    <select name="training_type" id="edit_training_type" class="field-input">
                        <option value="">Chọn hình thức đào tạo</option>
                        <option value="Chính quy">Chính quy</option>
                        <option value="Liên thông">Liên thông</option>
                        <option value="Từ xa">Từ xa</option>
                        <option value="Vừa làm vừa học">Vừa làm vừa học</option>
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

    <!-- Export Verification Modal -->
    <div id="exportVerificationModal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="mx-4 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-file-word mr-2 text-blue-600"></i>
                    Xuất công văn xác minh văn bằng
                </h3>
                <button type="button" onclick="closeExportModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="exportVerificationForm" method="GET"
                action="{{ route('student.export-verification', $student->student_id) }}">
                <div class="grid grid-cols-1 gap-4">
                    <div class="field-group">
                        <label for="don_vi_yeu_cau" class="field-label">Đơn vị yêu cầu</label>
                        <input type="text" name="don_vi_yeu_cau" id="don_vi_yeu_cau" class="field-input"
                            value="">
                    </div>

                    <div class="field-group">
                        <label for="so_cv_den" class="field-label">Số công văn đến</label>
                        <input type="text" name="so_cv_den" id="so_cv_den" class="field-input" value="">
                    </div>

                    <div class="field-group">
                        <label for="ngay_cv_den" class="field-label">Ngày công văn đến</label>
                        <input type="date" name="ngay_cv_den" id="ngay_cv_den" class="field-input" value="">
                    </div>
                </div>

                <div class="flex justify-end space-x-3 border-t pt-4">
                    <button type="button" onclick="closeExportModal()"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-gray-600 hover:bg-gray-50">
                        Hủy
                    </button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        Xuất file
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Adjustment Modal -->
    <div id="addAdjustmentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="mx-4 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-edit mr-2 text-purple-600"></i>
                    Thêm điều chỉnh thông tin văn bằng
                </h3>
                <button type="button" onclick="closeAdjustmentModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="addAdjustmentForm" method="POST" action="" class="space-y-4">
                @csrf

                <div class="field-group">
                    <label for="adjusted_field" class="field-label required">Trường thông tin cần điều chỉnh</label>
                    <select name="adjusted_field" id="adjusted_field" class="field-input" required
                        onchange="loadCurrentValue(this.value)">
                        <option value="">-- Chọn trường cần điều chỉnh --</option>
                        <option value="registration_number">Số đăng ký</option>
                        <option value="degree_type">Loại văn bằng</option>
                        <option value="major_name">Ngành/Chuyên ngành</option>
                        <option value="ranking">Xếp loại</option>
                        <option value="granting_date">Ngày cấp</option>
                        <option value="graduation_year">Năm tốt nghiệp</option>
                        <option value="decision_number">Số quyết định</option>
                        <option value="council_decision_number">Số QĐ thành lập hội đồng</option>
                        <option value="council_decision_date">Ngày QĐ thành lập hội đồng</option>
                        <option value="graduation_decision_number">Số QĐ công nhận tốt nghiệp</option>
                        <option value="graduation_decision_date">Ngày QĐ công nhận tốt nghiệp</option>
                        <option value="defense_date">Ngày bảo vệ</option>
                        <option value="training_start_date">Ngày bắt đầu đào tạo</option>
                        <option value="training_end_date">Ngày kết thúc đào tạo</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Chọn trường thông tin văn bằng cần điều chỉnh</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="field-group">
                        <label for="old_value" class="field-label">Giá trị hiện tại</label>
                        <input type="text" name="old_value" id="old_value" class="field-input bg-gray-50"
                            placeholder="Giá trị hiện tại" readonly>
                        <p class="mt-1 text-xs text-gray-500">Giá trị hiện tại (tự động lấy từ hệ thống)</p>
                    </div>

                    <div class="field-group">
                        <label for="new_value" class="field-label required">Giá trị mới</label>

                        <!-- Text input for most fields -->
                        <input type="text" name="new_value" id="new_value" class="field-input"
                            placeholder="Nhập giá trị mới" required>

                        <!-- Dropdown for degree_type -->
                        <select name="new_value" id="new_value_degree_type" class="field-input hidden" required>
                            <option value="">-- Chọn loại văn bằng --</option>
                            <option value="bachelor">Cử nhân</option>
                            <option value="master">Thạc sĩ</option>
                            <option value="doctor">Tiến sĩ</option>
                            <option value="certificate">Chứng chỉ</option>
                            <option value="engineer">Kỹ sư</option>
                        </select>

                        <!-- Dropdown for major_name -->
                        <select name="new_value" id="new_value_major_name" class="field-input hidden" required>
                            <option value="">-- Chọn ngành --</option>
                            @foreach ($majors as $major)
                                <option value="{{ $major->major_name }}">{{ $major->major_name }}</option>
                            @endforeach
                        </select>

                        <p class="mt-1 text-xs text-gray-500">Giá trị sau khi điều chỉnh</p>
                    </div>
                </div>

                <div class="field-group">
                    <label for="adjustment_content" class="field-label">Nội dung điều chỉnh <span
                            class="text-red-500">*</span></label>
                    <textarea name="adjustment_content" id="adjustment_content" rows="4" class="field-input" required
                        placeholder="Nhập nội dung điều chỉnh chi tiết..."></textarea>
                    <p class="mt-1 text-xs text-gray-500">Mô tả chi tiết nội dung điều chỉnh (bắt buộc).</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="field-group">
                        <label for="adjustment_decision_number" class="field-label">Số quyết định điều chỉnh <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="decision_number" id="adjustment_decision_number" class="field-input"
                            required placeholder="Nhập số QĐ">
                    </div>

                    <div class="field-group">
                        <x-vietnamese-date-input id="adjustment_decision_date" name="decision_date"
                            label="Ngày quyết định" :required="true" value="" inputClass="field-input" />
                    </div>
                </div>

                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle mr-3 mt-0.5 text-amber-600"></i>
                        <div class="text-sm text-amber-800">
                            <p class="font-medium">Lưu ý quan trọng:</p>
                            <ul class="mt-2 list-inside list-disc space-y-1">
                                <li>Mọi điều chỉnh sẽ được lưu vào lịch sử và không thể xóa</li>
                                <li>Thông tin người thực hiện sẽ tự động được ghi nhận</li>
                                <li>Vui lòng kiểm tra kỹ thông tin trước khi lưu</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 border-t pt-4">
                    <button type="button" onclick="closeAdjustmentModal()"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-gray-600 hover:bg-gray-50">
                        <i class="fas fa-times mr-2"></i>Hủy
                    </button>
                    <button type="submit" class="rounded-lg bg-purple-600 px-4 py-2 text-white hover:bg-purple-700">
                        <i class="fas fa-save mr-2"></i>Lưu điều chỉnh
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View All Adjustments Modal -->
    <div id="viewAdjustmentsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="mx-4 max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-lg bg-white p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-history mr-2 text-purple-600"></i>
                    Lịch sử điều chỉnh văn bằng
                </h3>
                <button type="button" onclick="closeViewAdjustmentsModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div id="adjustmentsTimeline" class="space-y-4">
                <!-- Timeline will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Add Reissue Modal -->
    <div id="addReissueModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="mx-4 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-sync-alt mr-2 text-blue-600"></i>
                    Thêm lịch sử cấp lại văn bằng
                </h3>
                <button type="button" onclick="closeReissueModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="addReissueForm" method="POST" action="" class="space-y-4">
                @csrf

                {{-- Display validation errors for reissue --}}
                <div id="reissueErrors" class="hidden rounded-md border border-red-200 bg-red-50 p-3">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Có lỗi xảy ra:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul id="reissueErrorList" class="list-inside list-disc space-y-1"></ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label for="reissue_old_blank" class="field-label">Phôi văn bằng hiện tại</label>
                    <input type="text" id="reissue_old_blank" class="field-input bg-gray-100" readonly>
                    <p class="mt-1 text-xs text-gray-500">Phôi văn bằng đang sử dụng</p>
                </div>

                <div class="field-group">
                    <label for="new_diploma_blank_id" class="field-label required">Chọn phôi văn bằng mới</label>
                    <select name="new_diploma_blank_id" id="new_diploma_blank_id" class="field-input" required>
                        <option value="">-- Chọn phôi từ kho --</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Chọn phôi văn bằng mới từ kho (cùng loại)</p>
                </div>

                <div class="field-group">
                    <label for="reissue_edit_content" class="field-label required">Nội dung chỉnh sửa</label>
                    <textarea name="edit_content" id="reissue_edit_content" rows="3" class="field-input"
                        placeholder="Mô tả nội dung chỉnh sửa, lý do cấp lại..." required></textarea>
                    <p class="mt-1 text-xs text-gray-500">Mô tả chi tiết lý do và nội dung chỉnh sửa</p>
                </div>

                <div class="field-group">
                    <label for="reissue_recall_decision" class="field-label required">Quyết định thu hồi, hủy bỏ và cấp
                        lại</label>
                    <input type="text" name="recall_decision" id="reissue_recall_decision" class="field-input"
                        placeholder="Số quyết định thu hồi và cấp lại" required>
                    <p class="mt-1 text-xs text-gray-500">Số và tên quyết định thu hồi, hủy bỏ văn bằng cũ và cấp lại</p>
                </div>

                <div class="field-group">
                    <label for="reissue_decision_date" class="field-label required">Ngày quyết định</label>
                    <input type="date" name="decision_date" id="reissue_decision_date" class="field-input"
                        required>
                    <p class="mt-1 text-xs text-gray-500">Ngày ban hành quyết định cấp lại</p>
                </div>

                <div class="rounded-lg border border-gray-300 bg-gray-50 p-4">
                    <h4 class="mb-3 text-sm font-semibold text-gray-700">
                        <i class="fas fa-clipboard-check mr-1"></i>
                        Xử lý phôi cũ <span class="text-red-500">*</span>
                    </h4>
                    <div class="space-y-2">
                        <div class="flex items-start">
                            <input type="radio" name="old_blank_status" id="status_recalled" value="recalled"
                                class="mt-1 h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="status_recalled" class="ml-2 text-sm">
                                <span class="font-medium text-gray-700">Đã thu hồi phôi cũ</span>
                                <p class="text-xs text-gray-500">Phôi cũ đã được thu hồi về trường (cập nhật trạng thái:
                                    Đã thu hồi)</p>
                            </label>
                        </div>
                        <div class="flex items-start">
                            <input type="radio" name="old_blank_status" id="status_destroyed" value="destroyed"
                                class="mt-1 h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                            <label for="status_destroyed" class="ml-2 text-sm">
                                <span class="font-medium text-gray-700">Đã hủy phôi cũ</span>
                                <p class="text-xs text-gray-500">Phôi cũ đã được hủy (cập nhật trạng thái: Đã hủy)</p>
                            </label>
                        </div>
                        <div class="flex items-start">
                            <input type="radio" name="old_blank_status" id="status_not_recalled"
                                value="not_recalled" checked
                                class="mt-1 h-4 w-4 border-gray-300 text-gray-600 focus:ring-gray-500">
                            <label for="status_not_recalled" class="ml-2 text-sm">
                                <span class="font-medium text-gray-700">Chưa thu hồi</span>
                                <p class="text-xs text-gray-500">Phôi cũ chưa được thu hồi (giữ nguyên trạng thái)</p>
                            </label>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-blue-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Lưu ý: Chọn một trong ba tùy chọn xử lý phôi cũ
                    </p>
                </div>

                <div class="field-group">
                    <label for="reissue_notes" class="field-label">Ghi chú</label>
                    <textarea name="notes" id="reissue_notes" rows="2" class="field-input"
                        placeholder="Ghi chú bổ sung (nếu có)"></textarea>
                </div>

                <div class="flex justify-end space-x-3 border-t pt-4">
                    <button type="button" onclick="closeReissueModal()"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-gray-600 hover:bg-gray-50">
                        <i class="fas fa-times mr-2"></i>Hủy
                    </button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Lưu lịch sử cấp lại
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Degree Confirmation Modal -->
    <div id="deleteDegreeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i>
                    Xác nhận xóa văn bằng
                </h3>
                <button type="button" onclick="closeDeleteDegreeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-gray-700">Bạn có chắc muốn xóa văn bằng <strong id="deleteDegreeNumber"
                        class="text-red-600"></strong>?</p>
                <p class="mt-2 text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    Văn bằng sẽ được xóa mềm và có thể khôi phục sau này.
                </p>
            </div>

            <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-4">
                <h4 class="mb-3 text-sm font-semibold text-gray-700">
                    <i class="fas fa-file-invoice mr-1"></i>
                    Tình trạng phôi văn bằng
                </h4>
                <div class="space-y-2">
                    <div class="flex items-start">
                        <input type="radio" id="recalled_yes" name="recalled_blank" value="1" checked
                            class="mt-1 h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="recalled_yes" class="ml-2 text-sm">
                            <span class="font-medium text-gray-700">Đã thu hồi phôi</span>
                            <p class="text-xs text-gray-600">Phôi đã được thu hồi và sẽ được trả về kho (Trong kho)</p>
                        </label>
                    </div>
                    <div class="flex items-start">
                        <input type="radio" id="recalled_no" name="recalled_blank" value="0"
                            class="mt-1 h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="recalled_no" class="ml-2 text-sm">
                            <span class="font-medium text-gray-700">Chưa thu hồi phôi</span>
                            <p class="text-xs text-gray-600">Phôi chưa được thu hồi (giữ nguyên trạng thái)</p>
                        </label>
                    </div>
                </div>
            </div>

            <form id="deleteDegreeForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <input type="hidden" name="recalled_blank" id="recalled_blank_input" value="1">

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteDegreeModal()"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-gray-600 hover:bg-gray-50">
                        <i class="fas fa-times mr-2"></i>Hủy
                    </button>
                    <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700">
                        <i class="fas fa-trash mr-2"></i>Xóa văn bằng
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Adjustment Modal Functions - Must be defined first for onclick handlers
        let currentDegreeData = null;

        window.openAdjustmentModal = function(degreeId) {
            try {
                const modal = document.getElementById('addAdjustmentModal');
                const form = document.getElementById('addAdjustmentForm');

                if (!modal || !form) {
                    alert('Không tìm thấy modal hoặc form');
                    return;
                }

                // Set form action
                form.action = `/degrees/${degreeId}/adjustments`;

                // Store degree data with all fields properly serialized
                const degreesData = @json($degrees);
                currentDegreeData = {
                    id: degreeId,
                    data: degreesData.find(d => d.degree_id === degreeId)
                };

                // Reset form
                form.reset();
                const oldValueInput = document.getElementById('old_value');
                const newValueInput = document.getElementById('new_value');
                if (oldValueInput) oldValueInput.value = '';
                if (newValueInput) newValueInput.value = '';

                // Show modal
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            } catch (error) {
                console.error('Error opening adjustment modal:', error);
                alert('Có lỗi khi mở modal điều chỉnh: ' + error.message);
            }
        };

        window.loadCurrentValue = function(fieldName) {
            try {
                if (!fieldName || !currentDegreeData || !currentDegreeData.data) {
                    return;
                }

                const degree = currentDegreeData.data;
                const oldValueInput = document.getElementById('old_value');
                const textInput = document.getElementById('new_value');
                const degreeTypeSelect = document.getElementById('new_value_degree_type');
                const majorNameSelect = document.getElementById('new_value_major_name');

                if (!degree || !oldValueInput) return;

                // Hide all inputs and remove name attribute
                textInput.classList.add('hidden');
                textInput.removeAttribute('name');
                textInput.removeAttribute('required');
                degreeTypeSelect.classList.add('hidden');
                degreeTypeSelect.removeAttribute('name');
                degreeTypeSelect.removeAttribute('required');
                majorNameSelect.classList.add('hidden');
                majorNameSelect.removeAttribute('name');
                majorNameSelect.removeAttribute('required');

                // Show appropriate input based on field type
                if (fieldName === 'degree_type') {
                    degreeTypeSelect.classList.remove('hidden');
                    degreeTypeSelect.setAttribute('name', 'new_value');
                    degreeTypeSelect.setAttribute('required', 'required');
                    degreeTypeSelect.value = degree[fieldName] || ''; // Pre-select current value
                } else if (fieldName === 'major_name') {
                    majorNameSelect.classList.remove('hidden');
                    majorNameSelect.setAttribute('name', 'new_value');
                    majorNameSelect.setAttribute('required', 'required');
                    majorNameSelect.value = degree[fieldName] || ''; // Pre-select current value
                } else {
                    textInput.classList.remove('hidden');
                    textInput.setAttribute('name', 'new_value');
                    textInput.setAttribute('required', 'required');
                    textInput.value = '';
                }

                let currentValue = degree[fieldName];

                // Map degree_type enum to Vietnamese for display
                if (fieldName === 'degree_type' && currentValue) {
                    const degreeTypeMap = {
                        'bachelor': 'Cử nhân',
                        'master': 'Thạc sĩ',
                        'doctor': 'Tiến sĩ',
                        'certificate': 'Chứng chỉ'
                    };
                    currentValue = degreeTypeMap[currentValue] || currentValue;
                }

                // Handle enum values (ranking, etc)
                if (typeof currentValue === 'object' && currentValue !== null) {
                    if (currentValue.name) {
                        currentValue = currentValue.name;
                    } else if (currentValue.value !== undefined) {
                        currentValue = currentValue.value;
                    }
                }

                // Format dates if needed
                if (fieldName.includes('_date') && currentValue) {
                    try {
                        const date = new Date(currentValue);
                        if (!isNaN(date.getTime())) {
                            currentValue = date.toLocaleDateString('vi-VN');
                        }
                    } catch (e) {
                        // Keep original value if date parsing fails
                    }
                }

                oldValueInput.value = currentValue || '(Chưa có giá trị)';
            } catch (error) {
                console.error('Error loading current value:', error);
            }
        };

        window.closeAdjustmentModal = function() {
            const modal = document.getElementById('addAdjustmentModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        };

        window.viewAllAdjustments = function(degreeId) {
            const modal = document.getElementById('viewAdjustmentsModal');
            const timeline = document.getElementById('adjustmentsTimeline');

            if (modal && timeline) {
                // Show loading state
                timeline.innerHTML = `
                    <div class="flex items-center justify-center py-8">
                        <i class="fas fa-spinner fa-spin text-3xl text-purple-600"></i>
                    </div>
                `;

                // Show modal
                modal.classList.remove('hidden');
                modal.style.display = 'flex';

                // Fetch adjustments
                fetch(`/degrees/${degreeId}/adjustments`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.adjustments.length > 0) {
                            const fieldLabels = {
                                'registration_number': 'Số đăng ký',
                                'degree_type': 'Loại văn bằng',
                                'major_name': 'Ngành/Chuyên ngành',
                                'ranking': 'Xếp loại',
                                'granting_date': 'Ngày cấp',
                                'graduation_year': 'Năm tốt nghiệp',
                                'decision_number': 'Số quyết định',
                                'council_decision_number': 'Số QĐ thành lập hội đồng',
                                'council_decision_date': 'Ngày QĐ thành lập hội đồng',
                                'graduation_decision_number': 'Số QĐ công nhận tốt nghiệp',
                                'graduation_decision_date': 'Ngày QĐ công nhận tốt nghiệp',
                                'defense_date': 'Ngày bảo vệ',
                                'training_start_date': 'Ngày bắt đầu đào tạo',
                                'training_end_date': 'Ngày kết thúc đào tạo'
                            };

                            // Map giá trị tiếng Anh sang tiếng Việt
                            const valueMapping = {
                                // Degree types
                                'certificate': 'Chứng chỉ',
                                'bachelor': 'Cử nhân',
                                'engineer': 'Kỹ sư',
                                'master': 'Thạc sĩ',
                                'doctor': 'Tiến sĩ',
                                // Rankings
                                'excellent': 'Xuất sắc',
                                'very_good': 'Giỏi',
                                'good': 'Khá',
                                'average': 'Trung bình',
                                'below_average': 'Trung bình khá'
                            };

                            // Helper function để convert giá trị
                            const convertValue = (value) => {
                                if (!value) return value;
                                return valueMapping[value.toLowerCase()] || value;
                            };

                            timeline.innerHTML = data.adjustments.map((adj, index) => `
                                <div class="relative flex gap-4 pb-8 ${index === data.adjustments.length - 1 ? '' : 'border-l-2 border-purple-200'} pl-6">
                                    <div class="absolute left-0 top-0 -translate-x-1/2 rounded-full bg-purple-600 p-2 text-white">
                                        <i class="fas fa-edit text-xs"></i>
                                    </div>
                                    <div class="flex-1 rounded-lg bg-white p-4 shadow">
                                        <div class="mb-2 flex items-start justify-between">
                                            <div class="flex-1">
                                                ${adj.changed_field ? `
                                                                                                        <p class="mb-1 text-xs font-semibold text-purple-700">
                                                                                                            <i class="fas fa-tag mr-1"></i>
                                                                                                            ${fieldLabels[adj.changed_field] || adj.changed_field}
                                                                                                        </p>
                                                                                                    ` : ''}
                                                ${adj.old_value && adj.new_value ? `
                                                                                                        <p class="mb-2 text-sm">
                                                                                                            <span class="rounded bg-red-100 px-2 py-0.5 text-red-700 line-through">${convertValue(adj.old_value)}</span>
                                                                                                            <i class="fas fa-arrow-right mx-2 text-gray-400"></i>
                                                                                                            <span class="rounded bg-green-100 px-2 py-0.5 text-green-700 font-medium">${convertValue(adj.new_value)}</span>
                                                                                                        </p>
                                                                                                    ` : ''}
                                                <h4 class="font-semibold text-gray-900">${adj.change_description}</h4>
                                            </div>
                                            <span class="text-xs text-gray-500">#${data.adjustments.length - index}</span>
                                        </div>
                                        <div class="mt-3 flex flex-wrap gap-4 text-sm text-gray-600">
                                            ${adj.decision_number ? `
                                                                                                    <span class="flex items-center">
                                                                                                        <i class="fas fa-file-contract mr-1 text-purple-600"></i>
                                                                                                        <strong>QĐ:</strong>&nbsp;${adj.decision_number}
                                                                                                    </span>
                                                                                                ` : ''}
                                            ${adj.decision_date ? `
                                                                                                    <span class="flex items-center">
                                                                                                        <i class="fas fa-calendar mr-1 text-purple-600"></i>
                                                                                                        <strong>Ngày:</strong>&nbsp;${adj.decision_date}
                                                                                                    </span>
                                                                                                ` : ''}
                                            ${adj.changed_by ? `
                                                                                                    <span class="flex items-center">
                                                                                                        <i class="fas fa-user mr-1 text-purple-600"></i>
                                                                                                        ${adj.changed_by.full_name || 'N/A'}
                                                                                                    </span>
                                                                                                ` : ''}
                                            <span class="flex items-center">
                                                <i class="fas fa-clock mr-1 text-purple-600"></i>
                                                ${new Date(adj.created_at).toLocaleString('vi-VN')}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            `).join('');
                        } else {
                            timeline.innerHTML = `
                                <div class="flex flex-col items-center justify-center py-8 text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-3"></i>
                                    <p>Chưa có lịch sử điều chỉnh</p>
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        timeline.innerHTML = `
                            <div class="flex flex-col items-center justify-center py-8 text-red-500">
                                <i class="fas fa-exclamation-triangle text-4xl mb-3"></i>
                                <p>Có lỗi xảy ra khi tải lịch sử điều chỉnh</p>
                            </div>
                        `;
                    });
            }
        };

        window.closeViewAdjustmentsModal = function() {
            const modal = document.getElementById('viewAdjustmentsModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        };

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

            // Set status - handle both enum object and string
            const statusValue = degree.status?.value || degree.status || 'Issued';
            document.getElementById('edit_status').value = statusValue;

            document.getElementById('edit_council_decision_number').value = degree.council_decision_number || '';
            document.getElementById('edit_graduation_decision_number').value = degree.graduation_decision_number || '';
            document.getElementById('edit_major_id').value = degree.major_id || '';
            document.getElementById('edit_training_type').value = degree.training_type || '';
            document.getElementById('edit_notes').value = degree.notes || '';

            // Set council decision date
            const councilDecisionDate = degree.council_decision_date ? degree.council_decision_date.split('T')[0] : '';
            document.getElementById('edit_council_decision_date').value = councilDecisionDate;

            // Format and set display input for council decision date
            if (councilDecisionDate) {
                const displayInput = document.getElementById('edit_council_decision_date_display');
                if (displayInput) {
                    const date = new Date(councilDecisionDate);
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    displayInput.value = `${day}/${month}/${year}`;
                }
            }

            // Set graduation decision date
            const graduationDecisionDate = degree.graduation_decision_date ? degree.graduation_decision_date.split('T')[0] :
                '';
            document.getElementById('edit_graduation_decision_date').value = graduationDecisionDate;

            // Format and set display input for graduation decision date
            if (graduationDecisionDate) {
                const displayInput = document.getElementById('edit_graduation_decision_date_display');
                if (displayInput) {
                    const date = new Date(graduationDecisionDate);
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    displayInput.value = `${day}/${month}/${year}`;
                }
            }

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
            // Get degree data to check status
            const degrees = @json($degrees);
            const degree = degrees.find(d => d.degree_id == degreeId);

            // Set degree info in modal
            document.getElementById('deleteDegreeNumber').textContent = registrationNumber;
            document.getElementById('deleteDegreeForm').action = `/degrees/${degreeId}/delete`;

            // Show modal
            const modal = document.getElementById('deleteDegreeModal');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';

            // Update hidden input when radio changes
            const radioButtons = document.querySelectorAll('input[name="recalled_blank"]');
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('recalled_blank_input').value = this.value;
                });
            });
        }

        function closeDeleteDegreeModal() {
            const modal = document.getElementById('deleteDegreeModal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
            // Reset form
            document.getElementById('deleteDegreeForm').reset();
            document.getElementById('recalled_yes').checked = true;
            document.getElementById('recalled_blank_input').value = '1';
        }

        // Export Verification Modal Functions
        const exportUrl = "{{ route('student.export-verification', $student->student_id) }}";

        function openExportModal() {
            const modal = document.getElementById('exportVerificationModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        }

        function closeExportModal() {
            const modal = document.getElementById('exportVerificationModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        }

        // Attach click interception to the existing export link(s)
        document.addEventListener('DOMContentLoaded', function() {
            try {
                document.querySelectorAll(`a[href="${exportUrl}"]`).forEach(a => {
                    a.addEventListener('click', function(e) {
                        e.preventDefault();
                        openExportModal();
                    });
                });
            } catch (err) {
                // silently ignore if route not present
            }
            // Close modal immediately when export form submits and disable submit button
            try {
                const exportForm = document.getElementById('exportVerificationForm');
                if (exportForm) {
                    exportForm.addEventListener('submit', function(e) {
                        // Close UI modal immediately so user sees it closed
                        closeExportModal();

                        // Disable submit button to prevent double submits
                        const submitBtn = exportForm.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        }

                        // allow normal form submission to proceed
                    });
                }
            } catch (err) {
                // ignore
            }
        });

        // Toggle Adjustment History
        function toggleAdjustmentHistory(degreeId) {
            const content = document.getElementById(`adjustment-history-${degreeId}`);
            const icon = document.getElementById(`toggle-icon-${degreeId}`);

            if (content && icon) {
                content.classList.toggle('expanded');
                icon.classList.toggle('rotated');
            }
        }

        // Toggle Student Change History
        function toggleStudentHistory() {
            const content = document.getElementById('student-change-history');
            const icon = document.getElementById('toggle-icon-student');

            if (content && icon) {
                content.classList.toggle('expanded');
                icon.classList.toggle('rotated');
            }
        }

        // Toggle Reissue History
        function toggleReissueHistory(degreeId) {
            const content = document.getElementById(`reissue-history-${degreeId}`);
            const icon = document.getElementById(`toggle-icon-reissue-${degreeId}`);

            if (content && icon) {
                content.classList.toggle('expanded');
                icon.classList.toggle('rotated');
            }
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const addModal = document.getElementById('addDegreeModal');
            const editModal = document.getElementById('editDegreeModal');
            const exportModal = document.getElementById('exportVerificationModal');
            const adjustmentModal = document.getElementById('addAdjustmentModal');
            const viewAdjustmentsModal = document.getElementById('viewAdjustmentsModal');
            const reissueModal = document.getElementById('addReissueModal');
            const deleteModal = document.getElementById('deleteDegreeModal');

            if (event.target === addModal) {
                closeAddDegreeModal();
            }

            if (event.target === editModal) {
                closeEditDegreeModal();
            }

            if (event.target === exportModal) {
                closeExportModal();
            }

            if (event.target === adjustmentModal) {
                closeAdjustmentModal();
            }

            if (event.target === viewAdjustmentsModal) {
                closeViewAdjustmentsModal();
            }

            if (event.target === reissueModal) {
                closeReissueModal();
            }

            if (event.target === deleteModal) {
                closeDeleteDegreeModal();
            }
        });

        // Reissue Modal Functions
        window.openReissueModal = function(degreeId, typeId, currentBlankSerial) {
            const modal = document.getElementById('addReissueModal');
            const form = document.getElementById('addReissueForm');
            const oldBlankInput = document.getElementById('reissue_old_blank');
            const newBlankSelect = document.getElementById('new_diploma_blank_id');

            if (!modal || !form || !oldBlankInput || !newBlankSelect) {
                alert('Không tìm thấy modal hoặc form');
                return;
            }

            // Set form action
            form.action = `/degrees/${degreeId}/reissues`;

            // Set old blank serial number
            oldBlankInput.value = currentBlankSerial || 'N/A';

            // Reset other fields
            newBlankSelect.innerHTML = '<option value="">-- Chọn phôi từ kho --</option>';
            document.getElementById('reissue_edit_content').value = '';
            document.getElementById('reissue_recall_decision').value = '';
            document.getElementById('reissue_decision_date').value = '';
            document.getElementById('reissue_notes').value = '';
            // Reset radio buttons to default (not_recalled)
            document.getElementById('status_not_recalled').checked = true;

            // Load available blanks
            if (typeId) {
                fetch(`/api/diploma-blanks/available?type_id=${typeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.blanks) {
                            data.blanks.forEach(blank => {
                                const option = document.createElement('option');
                                option.value = blank.diploma_blank_id;
                                option.textContent = `${blank.serial_number} - ${blank.type_name || ''}`;
                                newBlankSelect.appendChild(option);
                            });

                            if (data.blanks.length === 0) {
                                newBlankSelect.innerHTML = '<option value="">Không có phôi nào trong kho</option>';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error loading blanks:', error);
                        alert('Không thể tải danh sách phôi từ kho');
                    });
            }

            // Show modal
            modal.classList.remove('hidden');
            modal.style.display = 'flex';

            console.log('Modal opened, form action:', form.action);
        };

        // Add form submit listener for debugging
        document.addEventListener('DOMContentLoaded', function() {
            const reissueForm = document.getElementById('addReissueForm');
            if (reissueForm) {
                reissueForm.addEventListener('submit', function(e) {
                    console.log('Form submitting...');
                    console.log('Form action:', this.action);
                    console.log('Form data:', new FormData(this));

                    // Log all form fields
                    const formData = new FormData(this);
                    for (let pair of formData.entries()) {
                        console.log(pair[0] + ': ' + pair[1]);
                    }
                });
            }
        });

        window.closeReissueModal = function() {
            const modal = document.getElementById('addReissueModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        };

        window.deleteReissue = function(reissueId) {
            if (!confirm('Bạn có chắc chắn muốn xóa lịch sử cấp lại này?')) {
                return;
            }

            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/degrees/reissues/${reissueId}`;

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.content;
                form.appendChild(csrfInput);
            }

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        };
    </script>
@endsection
