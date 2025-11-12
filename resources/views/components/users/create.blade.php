@extends('layouts.default')

@section('content')
    <style>
        .field-description {
            margin-top: 0.5rem;
        }

        .alert-info {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
            background-color: #dbeafe;
            border: 1px solid #3b82f6;
            color: #1e40af;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6b7280;
        }

        .password-toggle:hover {
            color: #059669;
        }

        .password-field-wrapper {
            position: relative;
        }
    </style>

    <div class="student-edit-page">
        <!-- Page Header -->
        <div class="page-header">
            <div class="flex w-full items-center justify-between">
                <div>
                    <h1>Thêm Người Dùng Mới</h1>
                    <p>Nhập đầy đủ thông tin để tạo tài khoản người dùng mới</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="history.back()" class="btn btn-secondary">
                        Quay lại
                    </button>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('user.store') }}" class="space-y-8">
            @csrf

            <!-- User Information Section -->
            <div class="section-card">
                <h2 class="section-title">
                    Thông tin tài khoản
                </h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="form-field">
                        <label for="username" class="field-label">Tên đăng nhập <span class="text-red-500">*</span></label>
                        <input type="text" id="username" name="username" class="field-input"
                            value="{{ old('username') }}" placeholder="Nhập tên đăng nhập" required>
                        <div class="field-description">
                            <small class="text-gray-600">Tên đăng nhập phải là duy nhất trong hệ thống</small>
                        </div>
                        @error('username')
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

                    <div class="form-field">
                        <label for="email" class="field-label">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" class="field-input" value="{{ old('email') }}"
                            placeholder="Nhập địa chỉ email" required>
                        <div class="field-description">
                            <small class="text-gray-600">Email phải là địa chỉ hợp lệ và duy nhất</small>
                        </div>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="is_active" class="field-label">Trạng thái</label>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="is_active" name="is_active"
                                class="h-4 w-4 rounded border-gray-300 bg-gray-100 text-green-600 focus:ring-green-500"
                                {{ old('is_active', true) ? 'checked' : '' }}>
                            <label for="is_active" class="text-sm text-gray-700">Tài khoản hoạt động</label>
                        </div>
                        <div class="field-description">
                            <small class="text-gray-600">Tài khoản mới sẽ được kích hoạt theo mặc định</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Password Section -->
            <div class="section-card">
                <h2 class="section-title">
                    Thông tin mật khẩu
                </h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="form-field">
                        <label for="password" class="field-label">Mật khẩu <span class="text-red-500">*</span></label>
                        <div class="password-field-wrapper">
                            <input type="password" id="password" name="password" class="field-input pr-10"
                                placeholder="Nhập mật khẩu" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('password')"></i>
                        </div>
                        <div class="field-description">
                            <small class="text-gray-600">Mật khẩu phải có ít nhất 6 ký tự</small>
                        </div>
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="password_confirmation" class="field-label">Xác nhận mật khẩu <span
                                class="text-red-500">*</span></label>
                        <div class="password-field-wrapper">
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="field-input pr-10" placeholder="Nhập lại mật khẩu" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('password_confirmation')"></i>
                        </div>
                        @error('password_confirmation')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Status Information -->
                <div class="alert-info">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Lưu ý:</strong> Người dùng mới tạo sẽ có trạng thái "Hoạt động" theo mặc định.
                        Bạn có thể thay đổi trạng thái sau khi tạo tài khoản thành công.
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-section">
                <div class="last-updated">
                    Tạo tài khoản người dùng mới
                </div>
                <div class="action-buttons">
                    <button type="button" onclick="history.back()" class="btn-cancel">
                        Hủy
                    </button>
                    <button type="submit" class="btn-save" id="submitBtn">
                        Tạo người dùng
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

        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling;

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const requiredFields = form.querySelectorAll('input[required]');
            const submitBtn = document.getElementById('submitBtn');
            const password = document.getElementById('password');
            const passwordConfirmation = document.getElementById('password_confirmation');

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

                // Check password match
                if (password.value !== passwordConfirmation.value) {
                    isValid = false;
                    passwordConfirmation.classList.add('border-red-500');
                    alert('Mật khẩu xác nhận không khớp!');
                }

                if (!isValid) {
                    e.preventDefault();
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Tạo người dùng';
                }
            });

            // Real-time password match validation
            passwordConfirmation.addEventListener('input', function() {
                if (password.value !== this.value) {
                    this.classList.add('border-yellow-500');
                } else {
                    this.classList.remove('border-yellow-500');
                    this.classList.add('border-green-500');
                }
            });
        });
    </script>
@endsection
