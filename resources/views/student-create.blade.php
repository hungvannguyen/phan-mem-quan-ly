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

        .alert-info,
        .alert-warning {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }

        .alert-info {
            background-color: #dbeafe;
            border: 1px solid #3b82f6;
            color: #1e40af;
        }

        .alert-warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
        }

        .field-description {
            margin-top: 0.5rem;
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
                    <h1>Thêm Sinh Viên Mới</h1>
                    <p>Nhập đầy đủ thông tin sinh viên để tạo hồ sơ mới</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="history.back()" class="btn btn-secondary">
                        Quay lại
                    </button>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('student.save') }}" class="space-y-8">
            @csrf

            <!-- Student Information Section -->
            <div class="section-card">
                <h2 class="section-title">
                    Thông tin cơ bản
                </h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div class="form-field">
                        <label for="student_code" class="field-label">Mã sinh viên <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="student_code" name="student_code" class="field-input"
                            value="{{ old('student_code') }}" placeholder="Nhập mã sinh viên" required>
                        <div class="field-description">
                            <small class="text-gray-600">Mã sinh viên phải là duy nhất trong hệ thống</small>
                        </div>
                        @error('student_code')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="full_name" class="field-label">Họ và tên <span class="text-red-500">*</span></label>
                        <input type="text" id="full_name" name="full_name" class="field-input"
                            value="{{ old('full_name') }}" placeholder="Nhập họ và tên đầy đủ" required>
                        @error('full_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <x-vietnamese-date-input id="date_of_birth" name="date_of_birth" label="Ngày sinh" :required="true"
                        value="{{ old('date_of_birth') }}" description="Định dạng: ngày/tháng/năm (VD: 15/06/1995)">
                        @error('date_of_birth')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </x-vietnamese-date-input>

                    <div class="form-field">
                        <label for="gender" class="field-label">Giới tính <span class="text-red-500">*</span></label>
                        <select id="gender" name="gender" class="field-select" required>
                            <option value="">-- Chọn giới tính --</option>
                            @foreach (\App\Enums\StudentGender::cases() as $gender)
                                <option value="{{ $gender->value }}"
                                    {{ old('gender') == $gender->value ? 'selected' : '' }}>
                                    {{ $gender->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('gender')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="class_name" class="field-label">Lớp học</label>
                        <input type="text" id="class_name" name="class_name" class="field-input"
                            value="{{ old('class_name') }}" placeholder="Nhập tên lớp">
                        @error('class_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="major_id" class="field-label">Ngành đào tạo <span class="text-red-500">*</span></label>
                        <select id="major_id" name="major_id" class="field-select" required>
                            <option value="">-- Chọn ngành --</option>
                            @if (isset($majors))
                                @foreach ($majors as $major)
                                    <option value="{{ $major->major_id }}"
                                        {{ old('major_id') == $major->major_id ? 'selected' : '' }}>
                                        {{ $major->major_name }} ({{ $major->major_code }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('major_id')
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
                                    {{ old('status', 0) == $status->value ? 'selected' : '' }}>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        <div class="field-description">
                            <small class="text-gray-600">
                                Mặc định sinh viên mới sẽ có trạng thái "Đang học"
                            </small>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="nation" class="field-label">Dân tộc</label>
                        <input type="text" id="nation" name="nation" class="field-input"
                            value="{{ old('nation', 'Kinh') }}" placeholder="Nhập dân tộc">
                        @error('nation')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="nationality" class="field-label">Quốc tịch</label>
                        <input type="text" id="nationality" name="nationality" class="field-input"
                            value="{{ old('nationality', 'Việt Nam') }}" placeholder="Nhập quốc tịch">
                        @error('nationality')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Additional Information Section -->
            <div class="section-card">
                <h2 class="section-title">
                    Thông tin bổ sung
                </h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="form-field">
                        <label for="number_in_the_book" class="field-label">Số sổ gốc</label>
                        <input type="text" id="number_in_the_book" name="number_in_the_book" class="field-input"
                            value="{{ old('number_in_the_book') }}" placeholder="Nhập số sổ gốc">
                        @error('number_in_the_book')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        <div class="field-description">
                            <small class="text-gray-600">
                                Số thứ tự trong sổ gốc cấp văn bằng
                            </small>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="place_of_birth" class="field-label">Nơi sinh</label>
                        <input type="text" id="place_of_birth" name="place_of_birth" class="field-input"
                            value="{{ old('place_of_birth') }}" placeholder="Nhập nơi sinh">
                        @error('place_of_birth')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Status Information -->
                <div class="alert-info">
                    <div>
                        <strong>Lưu ý:</strong> Sinh viên mới tạo sẽ có trạng thái "Đang học" theo mặc định.
                        Bạn có thể thay đổi trạng thái sau khi tạo hồ sơ thành công.
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-section">
                <div class="last-updated">
                    Tạo hồ sơ sinh viên mới
                </div>
                <div class="action-buttons">
                    <button type="button" onclick="history.back()" class="btn-cancel">
                        Hủy
                    </button>
                    <button type="submit" class="btn-save" id="submitBtn">
                        Tạo sinh viên
                    </button>
                </div>
            </div>
        </form>

        @if (session('success'))
            <div class="flash-message success" id="success-message">
                <div class="flash-content">
                    <span>{{ session('success') }}</span>
                    <button onclick="document.getElementById('success-message').remove()" class="flash-close">
                        ×
                    </button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="flash-message error" id="error-message">
                <div class="flash-content">
                    <span>{{ session('error') }}</span>
                    <button onclick="document.getElementById('error-message').remove()" class="flash-close">
                        ×
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

        // Form validation and enhancements
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const requiredFields = form.querySelectorAll('input[required], select[required]');
            const statusSelect = document.getElementById('status');
            const submitBtn = document.getElementById('submitBtn');

            // Set default status to "Đang học" (0)
            if (statusSelect && !statusSelect.value) {
                statusSelect.value = '0';
            }

            // Handle status change information
            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    const selectedValue = parseInt(this.value);

                    // Show info when selecting "Đã tốt nghiệp"
                    if (selectedValue === 1) {
                        showNotification('✅ Sinh viên sẽ có thể được cấp văn bằng ngay sau khi tạo hồ sơ.',
                            'success');
                    }

                    // Show warning when selecting "Bỏ học"
                    if (selectedValue === 2) {
                        showNotification('⚠️ Sinh viên bỏ học sẽ không thể được cấp văn bằng.', 'warning');
                    }
                });
            }

            // Form validation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Đang tạo...';

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
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Tạo sinh viên';
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
                        ×
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
    </script>
@endsection
