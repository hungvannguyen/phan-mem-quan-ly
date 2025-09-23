@extends('layouts.default')

@section('content')
    <div class="student-edit-page">
        <!-- Page Header -->
        <div class="page-header">
            <div class="flex items-center justify-between">
                <div>
                    <h1>Chỉnh Sửa Thông Tin Sinh Viên</h1>
                    <p>Mã sinh viên: {{ $student->student_code }}</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="history.back()" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Quay lại
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

                    <div class="form-field">
                        <label for="date_of_birth" class="field-label">Ngày sinh <span class="text-red-500">*</span></label>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="field-input"
                            value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}" required>
                        @error('date_of_birth')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="class_name" class="field-label">Lớp học</label>
                        <input type="text" id="class_name" name="class_name" class="field-input"
                            value="{{ old('class_name', $student->class_name) }}">
                        @error('class_name')
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
                </div>
            </div>

            <!-- Degrees Information Section -->
            <div class="section-card">
                <h2 class="section-title">
                    <i class="fas fa-graduation-cap text-green-600"></i>
                    Văn bằng đã cấp
                    <span class="ml-auto text-sm font-normal text-gray-500">
                        ({{ $degrees->count() }} văn bằng)
                    </span>
                </h2>
                @if ($degrees->count() > 0)
                    <div class="space-y-4">
                        @foreach ($degrees as $index => $degree)
                            <div class="degree-card">
                                <div class="degree-header">
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
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
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

    <script>
        // Auto-hide success/error messages after 5 seconds
        setTimeout(() => {
            const successMsg = document.getElementById('success-message');
            const errorMsg = document.getElementById('error-message');
            if (successMsg) successMsg.remove();
            if (errorMsg) errorMsg.remove();
        }, 5000);

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const requiredFields = form.querySelectorAll('input[required], select[required]');

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
    </script>
@endsection
