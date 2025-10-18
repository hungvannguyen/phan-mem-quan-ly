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
        #addDegreeModal {
            z-index: 9999;
        }

        #addDegreeModal .bg-white {
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .grid-cols-2 {
                grid-template-columns: 1fr;
            }
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
                        <label for="nation" class="field-label">Dân tộc</label>
                        <input type="text" id="nation" name="nation" class="field-input"
                            value="{{ old('nation', $student->nation) }}" placeholder="Nhập dân tộc">
                        @error('nation')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="nationality" class="field-label">Quốc tịch</label>
                        <input type="text" id="nationality" name="nationality" class="field-input"
                            value="{{ old('nationality', $student->nationality) }}" placeholder="Nhập quốc tịch">
                        @error('nationality')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="number_in_the_book" class="field-label">Số sổ gốc</label>
                        <input type="text" id="number_in_the_book" name="number_in_the_book" class="field-input"
                            value="{{ old('number_in_the_book', $student->number_in_the_book) }}"
                            placeholder="Nhập số sổ gốc">
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

            <form id="addDegreeForm" onsubmit="handleAddDegree(event)" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="field-group">
                        <label for="degree_type" class="field-label required">Loại văn bằng</label>
                        <select name="degree_type" id="degree_type" class="field-input" required>
                            <option value="">Chọn loại văn bằng</option>
                            <option value="bachelor">Cử nhân</option>
                            <option value="master">Thạc sĩ</option>
                            <option value="doctor">Tiến sĩ</option>
                            <option value="certificate">Chứng chỉ</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label for="registration_number" class="field-label required">Số đăng ký</label>
                        <input type="text" name="registration_number" id="registration_number" class="field-input"
                            placeholder="Nhập số đăng ký" required>
                    </div>

                    <div class="field-group">
                        <label for="graduation_year" class="field-label required">Năm tốt nghiệp</label>
                        <input type="number" name="graduation_year" id="graduation_year" class="field-input"
                            placeholder="Ví dụ: 2024" min="1990" max="{{ date('Y') }}" required>
                    </div>

                    <div class="field-group">
                        <label for="granting_date" class="field-label required">Ngày cấp</label>
                        <input type="date" name="granting_date" id="granting_date" class="field-input" required>
                    </div>

                    <div class="field-group">
                        <label for="ranking" class="field-label">Xếp loại</label>
                        <select name="ranking" id="ranking" class="field-input">
                            <option value="">Chọn xếp loại</option>
                            <option value="Xuất sắc">Xuất sắc</option>
                            <option value="Giỏi">Giỏi</option>
                            <option value="Khá">Khá</option>
                            <option value="Trung bình">Trung bình</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label for="decision_number" class="field-label">Số quyết định</label>
                        <input type="text" name="decision_number" id="decision_number" class="field-input"
                            placeholder="Nhập số quyết định">
                    </div>
                </div>

                <div class="field-group">
                    <label for="major_name" class="field-label">Chuyên ngành</label>
                    <input type="text" name="major_name" id="major_name" class="field-input"
                        placeholder="Nhập tên chuyên ngành">
                </div>

                <div class="field-group">
                    <label for="notes" class="field-label">Ghi chú</label>
                    <textarea name="notes" id="notes" rows="3" class="field-input" placeholder="Nhập ghi chú (tùy chọn)"></textarea>
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
        function openAddDegreeModal() {
            document.getElementById('addDegreeModal').style.display = 'flex';
        }

        function closeAddDegreeModal() {
            document.getElementById('addDegreeModal').style.display = 'none';
            // Reset form
            document.getElementById('addDegreeForm').reset();
        }

        // Handle add degree form submission
        function handleAddDegree(event) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);

            // Add student ID
            formData.append('student_id', {{ $student->id }});

            // Show loading
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang lưu...';
            submitBtn.disabled = true;

            fetch('{{ route('degrees.store') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Thêm văn bằng thành công!', 'success');
                        closeAddDegreeModal();
                        // Reload page to show new degree
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showNotification(data.message || 'Có lỗi xảy ra!', 'error');
                    }
                })
                .catch(error => {
                    showNotification('Có lỗi xảy ra khi thêm văn bằng!', 'error');
                    console.error('Error:', error);
                })
                .finally(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('addDegreeModal');
            if (event.target === modal) {
                closeAddDegreeModal();
            }
        });
    </script>
@endsection
