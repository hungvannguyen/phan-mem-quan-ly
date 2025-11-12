@extends('layouts.default')

@section('content')
    <style>
        .field-description {
            margin-top: 0.5rem;
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
    </style>

    <div class="student-edit-page">
        <!-- Page Header -->
        <div class="page-header">
            <div class="flex w-full items-center justify-between">
                <div>
                    <h1>Chỉnh Sửa Thông Tin Người Dùng</h1>
                    <p>Tên đăng nhập: {{ $user->username }}</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="history.back()" class="btn btn-secondary">
                        Quay lại
                    </button>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('user.update', $user->user_id) }}" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- User Information Section -->
            <div class="section-card">
                <h2 class="section-title">
                    <i class="fas fa-user-edit text-blue-600"></i>
                    Thông tin tài khoản
                </h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="form-field">
                        <label for="username" class="field-label">Tên đăng nhập <span class="text-red-500">*</span></label>
                        <input type="text" id="username" name="username" class="field-input"
                            value="{{ old('username', $user->username) }}" placeholder="Nhập tên đăng nhập" required>
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
                            value="{{ old('full_name', $user->full_name) }}" placeholder="Nhập họ và tên đầy đủ" required>
                        @error('full_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="email" class="field-label">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" class="field-input"
                            value="{{ old('email', $user->email) }}" placeholder="Nhập địa chỉ email" required>
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
                                {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                @if (Auth::id() === $user->user_id) disabled @endif>
                            <label for="is_active" class="text-sm text-gray-700">Tài khoản hoạt động</label>
                        </div>
                        @if (Auth::id() === $user->user_id)
                            <div class="field-description">
                                <small class="text-yellow-600"><i class="fas fa-lock"></i> Không thể thay đổi trạng thái tài
                                    khoản của chính bạn</small>
                            </div>
                        @endif
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
                            <i class="fas fa-toggle-on"></i>
                            <span>Trạng thái hiện tại</span>
                        </div>
                        <div class="status-value">
                            @if ($user->is_active)
                                <span class="text-green-600"><i class="fas fa-check-circle"></i> Hoạt động</span>
                            @else
                                <span class="text-red-600"><i class="fas fa-times-circle"></i> Vô hiệu hóa</span>
                            @endif
                        </div>
                    </div>

                    <div class="status-card">
                        <div class="status-header">
                            <i class="fas fa-calendar-plus"></i>
                            <span>Ngày tạo</span>
                        </div>
                        <div class="status-value">
                            <span class="text-sm">{{ $user->created_at?->format('d/m/Y H:i') ?? 'Chưa xác định' }}</span>
                        </div>
                    </div>

                    <div class="status-card">
                        <div class="status-header">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Ngày cập nhật</span>
                        </div>
                        <div class="status-value">
                            <span class="text-sm">{{ $user->updated_at?->format('d/m/Y H:i') ?? 'Chưa cập nhật' }}</span>
                        </div>
                    </div>
                </div>

                @if (Auth::id() === $user->user_id)
                    <div class="alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <strong>Lưu ý:</strong> Bạn đang chỉnh sửa tài khoản của chính mình.
                            Một số tính năng có thể bị hạn chế để bảo vệ tài khoản.
                        </div>
                    </div>
                @endif
            </div>

            <!-- Password Section -->
            <div class="section-card">
                <h2 class="section-title">
                    <i class="fas fa-key text-yellow-600"></i>
                    Đổi mật khẩu
                </h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="form-field">
                        <label for="password" class="field-label">Mật khẩu mới</label>
                        <div class="password-field-wrapper">
                            <input type="password" id="password" name="password" class="field-input pr-10"
                                placeholder="Để trống nếu không đổi mật khẩu">
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
                        <label for="password_confirmation" class="field-label">Xác nhận mật khẩu mới</label>
                        <div class="password-field-wrapper">
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="field-input pr-10" placeholder="Nhập lại mật khẩu mới">
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('password_confirmation')"></i>
                        </div>
                        @error('password_confirmation')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="alert-info">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Lưu ý:</strong> Để trống các trường mật khẩu nếu bạn không muốn thay đổi mật khẩu.
                        Chỉ khi nhập mật khẩu mới thì mật khẩu mới sẽ được cập nhật.
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-section">
                <div class="last-updated">
                    <i class="fas fa-info-circle"></i>
                    Cập nhật lần cuối: {{ $user->updated_at?->format('d/m/Y H:i') ?? 'Chưa cập nhật' }}
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
            const password = document.getElementById('password');
            const passwordConfirmation = document.getElementById('password_confirmation');

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

                // Check password match if password is provided
                if (password.value && password.value !== passwordConfirmation.value) {
                    isValid = false;
                    passwordConfirmation.classList.add('border-red-500');
                    alert('Mật khẩu xác nhận không khớp!');
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });

            // Real-time password match validation
            passwordConfirmation.addEventListener('input', function() {
                if (password.value && password.value !== this.value) {
                    this.classList.add('border-yellow-500');
                } else if (password.value) {
                    this.classList.remove('border-yellow-500');
                    this.classList.add('border-green-500');
                }
            });
        });
    </script>
@endsection
